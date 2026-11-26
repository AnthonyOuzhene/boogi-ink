<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * Le POST est intercepté par le pare-feu (form_login, security.yaml), pas traité ici.
     * Sous le préfixe du backoffice pour ne pas le signaler par /login ; accessible
     * sans connexion grâce à PUBLIC_ACCESS dans security.yaml.
     */
    #[Route('/boogi-0806/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('admin');
        }

        return $this->render('@EasyAdmin/page/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
            'page_title' => "Boogi'ink — Administration",
            'csrf_token_intention' => 'authenticate',
            'target_path' => $this->generateUrl('admin'),
            'username_label' => 'Email',
            'password_label' => 'Mot de passe',
            'sign_in_label' => 'Se connecter',
        ]);
    }

    // Jamais exécutée : le pare-feu intercepte l'URL (clé "logout" de security.yaml)
    #[Route('/boogi-0806/logout', name: 'app_logout')]
    public function logout(): never
    {
        throw new \LogicException('Intercepté par le pare-feu.');
    }
}
