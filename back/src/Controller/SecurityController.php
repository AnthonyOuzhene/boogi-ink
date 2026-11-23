<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * Affiche le formulaire de connexion.
     * La vérification email / mot de passe n'est PAS faite ici : c'est le
     * pare-feu (form_login dans security.yaml) qui intercepte le POST sur cette même URL.
     * Rangée sous le préfixe du backoffice : /login n'indique pas qu'il y a un backoffice.
     * Elle reste accessible sans être connecté grâce à la règle PUBLIC_ACCESS de security.yaml.
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

    /**
     * Cette méthode n'est jamais exécutée : le pare-feu intercepte cette URL
     * (clé "logout" dans security.yaml). La route doit juste exister.
     */
    #[Route('/boogi-0806/logout', name: 'app_logout')]
    public function logout(): never
    {
        throw new \LogicException('Intercepté par le pare-feu.');
    }
}
