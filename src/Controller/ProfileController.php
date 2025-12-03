<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profil')]
#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('', name: 'app_profil')]
    public function index(): Response
    {
        $user = $this->getUser();

        // Sélectionner le layout en fonction du rôle
        $baseLayout = '_layouts/user_base.html.twig';

        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            $baseLayout = '_layouts/superadmin_base.html.twig';
        } elseif ($this->isGranted('ROLE_ADMIN')) {
            $baseLayout = '_layouts/admin_base.html.twig';
        }

        return $this->render('profil/index.html.twig', [
            'user' => $user,
            'base_layout' => $baseLayout,
        ]);
    }
}


