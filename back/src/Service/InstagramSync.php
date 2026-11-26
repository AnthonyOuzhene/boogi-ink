<?php

namespace App\Service;

use App\Entity\Home;
use App\Entity\InstagramConnection;
use App\Entity\InstagramPost;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Récupère les derniers posts Instagram du salon et les enregistre dans le site (InstagramPost).
 *
 * API utilisée : "Instagram API with Instagram Login" (graph.instagram.com). Elle demande
 * un compte Instagram professionnel et un jeton longue durée (60 jours), renouvelé ici
 * automatiquement quand il expire dans moins de 10 jours.
 *
 * En cas de panne (Instagram injoignable, jeton invalide…) : on garde les posts déjà
 * enregistrés, et l'erreur est notée pour le tableau de bord du backoffice.
 */
final class InstagramSync
{
    private const API_URL = 'https://graph.instagram.com';
    private const POST_COUNT = 6;          // nombre de posts affichés sur l'accueil
    private const REFRESH_BEFORE = '+10 days';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EntityManagerInterface $entityManager,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    public function getConnection(): InstagramConnection
    {
        $connection = $this->entityManager->getRepository(InstagramConnection::class)->findOneBy([]);
        if (null === $connection) {
            $connection = new InstagramConnection();
            $this->entityManager->persist($connection);
        }

        return $connection;
    }

    /**
     * Jeton longue durée généré depuis l'application Meta : valable 60 jours,
     * on compte 59 par prudence.
     */
    public function saveToken(string $token): void
    {
        $this->getConnection()
            ->setAccessToken(trim($token))
            ->setTokenExpiresAt(new \DateTimeImmutable('+59 days'))
            ->setLastError(null);
        $this->entityManager->flush();
    }

    /**
     * @return int nombre de posts affichés
     *
     * @throws \RuntimeException si aucun jeton n'est enregistré ou si Instagram répond une erreur
     */
    public function sync(): int
    {
        $connection = $this->getConnection();
        if (!$connection->isConnected()) {
            throw new \RuntimeException('Aucun jeton Instagram enregistré (commande app:instagram:token).');
        }

        try {
            $this->refreshTokenIfNeeded($connection);

            $media = $this->httpClient->request('GET', self::API_URL.'/me/media', [
                'query' => [
                    'fields' => 'id,caption,media_type,media_url,thumbnail_url,permalink,timestamp',
                    'limit' => self::POST_COUNT,
                    'access_token' => $connection->getAccessToken(),
                ],
            ])->toArray()['data'] ?? [];

            $posts = [];
            foreach (array_slice($media, 0, self::POST_COUNT) as $item) {
                $posts[] = [
                    'id' => (string) $item['id'],
                    'permalink' => $item['permalink'],
                    'caption' => $item['caption'] ?? null,
                    'mediaType' => $item['media_type'],
                    // Reel : l'image de couverture (thumbnail_url), jamais la vidéo
                    'imageUrl' => 'VIDEO' === $item['media_type'] ? ($item['thumbnail_url'] ?? null) : ($item['media_url'] ?? null),
                    'postedAt' => new \DateTimeImmutable($item['timestamp']),
                ];
            }

            $count = $this->replacePosts($posts, fn (string $url) => $this->httpClient->request('GET', $url)->getContent());

            $connection->setLastSyncAt(new \DateTimeImmutable())->setLastError(null);
            $this->entityManager->flush();

            return $count;
        } catch (\Throwable $e) {
            // Les posts déjà enregistrés restent affichés
            $message = $this->describeError($e, $connection->getAccessToken());
            $connection->setLastError($message);
            $this->entityManager->flush();

            throw new \RuntimeException('Synchronisation Instagram impossible : '.$message);
        }
    }

    /**
     * Message d'erreur lisible et SANS le jeton : les erreurs HTTP de Symfony contiennent
     * l'adresse appelée, jeton compris ; ce message est enregistré et affiché dans le backoffice.
     */
    private function describeError(\Throwable $e, ?string $token): string
    {
        $message = $e->getMessage();

        if ($e instanceof HttpExceptionInterface) {
            $response = $e->getResponse();
            // Instagram détaille l'erreur dans le JSON (ex. jeton expiré)
            $message = sprintf('Instagram a répondu %d : %s', $response->getStatusCode(), $response->toArray(false)['error']['message'] ?? 'erreur inconnue');
        }

        return $token ? str_replace($token, '***', $message) : $message;
    }

    /**
     * Posts d'exemple (photos des fixtures) pour voir la bande sur l'accueil avant d'avoir
     * le jeton ; remplacés à la première synchronisation.
     */
    public function loadDemo(): int
    {
        $profile = $this->entityManager->getRepository(Home::class)->findOneBy([])?->getInstagramUrl() ?? 'https://www.instagram.com/';
        $files = ['tattoo1.jpg', 'tattoo2.png', 'tattoo3.jpg', 'tattoo5.jpg', 'tattoo6.jpg', 'banner.jpg'];
        $captions = [
            'Fine line terminé aujourd\'hui, merci pour la confiance ✨',
            'Flash dispo cette semaine, premier arrivé premier servi !',
            'Retouche cicatrisée, 3 semaines après la séance.',
            'Timelapse de la séance d\'hier ▶',
            'Nouveau dessin sur la planche de flashs.',
            'L\'atelier est prêt pour la journée.',
        ];

        $posts = [];
        foreach ($files as $index => $file) {
            $posts[] = [
                'id' => 'demo-'.($index + 1),
                'permalink' => $profile,
                'caption' => $captions[$index],
                'mediaType' => 3 === $index ? 'VIDEO' : 'IMAGE',
                'imageUrl' => $this->projectDir.'/fixtures/images/'.$file,
                'postedAt' => new \DateTimeImmutable(sprintf('-%d days', $index * 2 + 1)),
            ];
        }

        $count = $this->replacePosts($posts, fn (string $path) => file_get_contents($path));
        $this->entityManager->flush();

        return $count;
    }

    /** Moins de 10 jours avant expiration : on obtient un nouveau jeton de 60 jours */
    private function refreshTokenIfNeeded(InstagramConnection $connection): void
    {
        $expiresAt = $connection->getTokenExpiresAt();
        if (null !== $expiresAt && $expiresAt > new \DateTimeImmutable(self::REFRESH_BEFORE)) {
            return;
        }

        $response = $this->httpClient->request('GET', self::API_URL.'/refresh_access_token', [
            'query' => ['grant_type' => 'ig_refresh_token', 'access_token' => $connection->getAccessToken()],
        ])->toArray();

        $connection
            ->setAccessToken($response['access_token'])
            ->setTokenExpiresAt(new \DateTimeImmutable(sprintf('+%d seconds', (int) $response['expires_in'])));
    }

    /**
     * Les nouveaux posts sont ajoutés (image copiée), les autres supprimés avec leur image.
     *
     * @param list<array{id: string, permalink: string, caption: ?string, mediaType: string, imageUrl: ?string, postedAt: \DateTimeImmutable}> $posts
     * @param callable(string): string $download lit le contenu de l'image (adresse web ou fichier local)
     */
    private function replacePosts(array $posts, callable $download): int
    {
        $repository = $this->entityManager->getRepository(InstagramPost::class);
        $directory = $this->projectDir.'/public/'.InstagramPost::UPLOAD_PATH;
        $filesystem = new Filesystem();
        $filesystem->mkdir($directory);

        $keptIds = [];
        foreach ($posts as $data) {
            if (null === $data['imageUrl']) {
                continue; // post sans image exploitable
            }
            $keptIds[] = $data['id'];

            $post = $repository->findOneBy(['instagramId' => $data['id']]);
            if (null === $post) {
                // Image convertie en WebP à l'enregistrement
                $filename = preg_replace('/[^A-Za-z0-9_-]/', '', $data['id']).'.img';
                $filesystem->dumpFile($directory.'/'.$filename, $download($data['imageUrl']));
                $post = (new InstagramPost())->setInstagramId($data['id'])->setImage($filename);
                $this->entityManager->persist($post);
            }

            // La légende ou le lien ont pu changer sur Instagram
            $post
                ->setPermalink($data['permalink'])
                ->setCaption($data['caption'])
                ->setMediaType($data['mediaType'])
                ->setPostedAt($data['postedAt']);
        }

        foreach ($repository->findAll() as $post) {
            if (!in_array($post->getInstagramId(), $keptIds, true)) {
                $filesystem->remove($directory.'/'.$post->getImage());
                $this->entityManager->remove($post);
            }
        }

        return count($keptIds);
    }
}
