<?php

namespace App\Service;

use App\Entity\Artist;
use App\Entity\Flash;
use App\Entity\Gallery;
use App\Entity\Picture;
use App\Entity\StudioPhoto;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Allège les photos envoyées depuis le backoffice : réduites à 2 000 px au plus sur le grand côté,
 * puis converties en WebP (qualité 80, perte invisible à l'œil). L'original est supprimé.
 * Une photo de téléphone (4 000 px, 3 à 8 Mo) passe ainsi à quelques centaines de Ko.
 *
 * Appelé automatiquement à chaque enregistrement (ImageWebpListener) et par la commande
 * app:images:convert-webp pour les photos déjà envoyées. Utilise l'extension GD de PHP.
 */
final class ImageOptimizer
{
    private const MAX_SIZE = 2000; // px, sur le plus grand côté
    private const QUALITY = 80;    // WebP : 0 à 100

    /**
     * Champs "photo" de chaque entité => dossier d'envoi (relatif à public/).
     * Nouveau champ ImageField dans le backoffice ? L'ajouter ici pour qu'il soit converti.
     */
    public const IMAGE_FIELDS = [
        Gallery::class => ['mainPicture' => Gallery::UPLOAD_PATH, 'sketch' => Gallery::UPLOAD_PATH],
        Picture::class => ['filename' => Gallery::UPLOAD_PATH],
        Flash::class => ['picture' => Flash::UPLOAD_PATH],
        StudioPhoto::class => ['picture' => StudioPhoto::UPLOAD_PATH],
        Artist::class => ['picture' => Artist::UPLOAD_PATH, 'atelierPicture' => Artist::UPLOAD_PATH],
    ];

    public function __construct(
        #[Autowire('%kernel.project_dir%/public')]
        private readonly string $publicDir,
    ) {
    }

    /**
     * Convertit toutes les photos de l'entité qui ne sont pas encore en WebP,
     * et met à jour les noms de fichiers dans l'entité.
     *
     * @return int nombre d'octets gagnés (0 si rien n'a changé)
     */
    public function optimizeEntity(object $entity, ClassMetadata $metadata): int
    {
        $saved = 0;

        foreach (self::IMAGE_FIELDS[$metadata->getName()] ?? [] as $field => $uploadPath) {
            $filename = $metadata->getFieldValue($entity, $field);
            if (!$filename) {
                continue;
            }

            $result = $this->toWebp($this->publicDir.'/'.$uploadPath, $filename);
            if (null !== $result) {
                $metadata->setFieldValue($entity, $field, $result['filename']);
                $saved += $result['saved'];
            }
        }

        return $saved;
    }

    /**
     * @return array{filename: string, saved: int}|null null si le fichier est déjà en WebP,
     *                                                  introuvable ou dans un format non géré
     */
    private function toWebp(string $directory, string $filename): ?array
    {
        if ('webp' === strtolower(pathinfo($filename, PATHINFO_EXTENSION))) {
            return null;
        }

        $source = $directory.'/'.$filename;
        $webpName = pathinfo($filename, PATHINFO_FILENAME).'.webp';
        $target = $directory.'/'.$webpName;

        if (!is_file($source)) {
            // Déjà converti via une autre fiche qui partageait le même fichier : on pointe sur le WebP
            return is_file($target) ? ['filename' => $webpName, 'saved' => 0] : null;
        }

        $image = $this->load($source);
        if (null === $image) {
            return null; // format non géré (GIF animé…) : on garde l'original
        }

        $image = $this->resize($image);
        $originalSize = filesize($source);
        $ok = imagewebp($image, $target, self::QUALITY);
        imagedestroy($image);

        if (!$ok) {
            return null;
        }

        unlink($source);

        return ['filename' => $webpName, 'saved' => max(0, $originalSize - filesize($target))];
    }

    private function load(string $path): ?\GdImage
    {
        $type = @getimagesize($path)[2] ?? null;

        $image = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            default => false,
        };
        if (!$image) {
            return null;
        }

        if (IMAGETYPE_PNG === $type) {
            // Garde la transparence des PNG (dessins détourés, logos)
            imagepalettetotruecolor($image);
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        if (IMAGETYPE_JPEG === $type) {
            $image = $this->fixOrientation($image, $path);
        }

        return $image;
    }

    /**
     * Les photos de téléphone sont souvent enregistrées "couchées", avec une indication de rotation
     * (EXIF) que le navigateur applique. Le WebP ne la garde pas : on fait la rotation pour de bon.
     */
    private function fixOrientation(\GdImage $image, string $path): \GdImage
    {
        $orientation = function_exists('exif_read_data') ? (@exif_read_data($path)['Orientation'] ?? 1) : 1;

        $rotated = match ((int) $orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };

        return $rotated ?: $image;
    }

    private function resize(\GdImage $image): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        if (max($width, $height) <= self::MAX_SIZE) {
            return $image;
        }

        $ratio = self::MAX_SIZE / max($width, $height);
        $resized = imagescale($image, (int) round($width * $ratio), (int) round($height * $ratio), IMG_BICUBIC);
        if (!$resized) {
            return $image;
        }

        // imagescale ne garde pas toujours la transparence : on la réactive
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagedestroy($image);

        return $resized;
    }
}
