<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/settings')]
#[IsGranted('ROLE_USER')]
class SettingsController extends AbstractController
{
    #[Route('', name: 'app_settings')]
    public function index(): Response
    {
        $user = $this->getUser();

        // Layout en fonction du rôle (même logique que profil / help)
        $baseLayout = '_layouts/user_base.html.twig';

        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            $baseLayout = 'superadmin_base.html.twig';
        } elseif ($this->isGranted('ROLE_ADMIN')) {
            $baseLayout = 'admin_base.html.twig';
        }

        return $this->render('settings/index.html.twig', [
            'user' => $user,
            'base_layout' => $baseLayout,
        ]);
    }
}


