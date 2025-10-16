<?php

namespace App\Controller;

use App\Repository\LivreRepository;
use App\Repository\AuteurRepository;
use App\Repository\CategorieRepository;
use App\Repository\EditeurRepository;
use App\Repository\OuvrierRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AccueilController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    #[Route('/accueil', name: 'app_accueil_alt')]
    public function index(

        LivreRepository $livreRepo,
        AuteurRepository $auteurRepo,
        CategorieRepository $categorieRepo,
        EditeurRepository $editeurRepo,
        OuvrierRepository $ouvrierRepo
    ): Response {
        $stats = [
            'livres' => $livreRepo->count([]),
            'auteurs' => $auteurRepo->count([]),
            'categories' => $categorieRepo->count([]),
            'editeurs' => $editeurRepo->count([]),
            'ouvriers' => $ouvrierRepo->count([]),
        ];

        $recentLivres = $livreRepo->findBy([], ['id' => 'DESC'], 5);

        return $this->render('accueil/index.html.twig', [
            'stats' => $stats,
            'recentLivres' => $recentLivres,
        ]);
    }
}