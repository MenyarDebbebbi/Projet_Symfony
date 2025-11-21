<?php

namespace App\Controller\Admin;

use App\Entity\Auteur;
use App\Entity\Categorie;
use App\Entity\Editeur;
use App\Entity\Livre;
use App\Entity\Ouvrier;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Administration')
            ->setFaviconPath('favicon.ico')
            ->setTranslationDomain('admin')
            ->renderSidebarMinimized();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::section('Gestion des Livres');
        yield MenuItem::linkToCrud('Livres', 'fa fa-book', Livre::class);
        yield MenuItem::linkToCrud('Auteurs', 'fa fa-user', Auteur::class);
        yield MenuItem::linkToCrud('Catégories', 'fa fa-tags', Categorie::class);
        yield MenuItem::linkToCrud('Éditeurs', 'fa fa-building', Editeur::class);
        yield MenuItem::section('Gestion des Utilisateurs');
        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-users', User::class);
        yield MenuItem::linkToCrud('Ouvriers', 'fa fa-briefcase', Ouvrier::class);
        yield MenuItem::section();
        yield MenuItem::linkToLogout('Déconnexion', 'fa fa-sign-out');
    }
}
