<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('login');
        }

        // Redirection selon le rôle
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('superadmin_dashboard');
        } elseif (in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('admin_dashboard');
        } else {
            return $this->redirectToRoute('user_dashboard');
        }
    }
}

