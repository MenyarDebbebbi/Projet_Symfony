<?php

namespace App\Controller;

use App\Entity\Auteur;
use App\Entity\Bibliotheque;
use App\Entity\Categorie;
use App\Entity\Editeur;
use App\Entity\Livre;
use App\Form\AuteurType;
use App\Form\CategorieType;
use App\Form\EditeurType;
use App\Form\LivreType;
use App\Repository\AuteurRepository;
use App\Repository\BibliothequeRepository;
use App\Repository\CategorieRepository;
use App\Repository\EditeurRepository;
use App\Repository\LivreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'admin_dashboard')]
    public function dashboard(
        LivreRepository $livreRepository,
        AuteurRepository $auteurRepository,
        CategorieRepository $categorieRepository,
        EditeurRepository $editeurRepository
    ): Response {
        $user = $this->getUser();
        $bibliotheque = $user->getBibliotheque();

        $totalLivres = $bibliotheque 
            ? $livreRepository->count(['bibliotheque' => $bibliotheque])
            : 0;
        $totalAuteurs = $auteurRepository->count([]);
        $totalCategories = $categorieRepository->count([]);
        $totalEditeurs = $editeurRepository->count([]);

        return $this->render('admin/dashboard.html.twig', [
            'bibliotheque' => $bibliotheque,
            'totalLivres' => $totalLivres,
            'totalAuteurs' => $totalAuteurs,
            'totalCategories' => $totalCategories,
            'totalEditeurs' => $totalEditeurs,
        ]);
    }

    // Gestion Bibliothèque
    #[Route('/bibliotheque', name: 'admin_bibliotheque')]
    public function bibliotheque(): Response
    {
        $user = $this->getUser();
        $bibliotheque = $user->getBibliotheque();

        return $this->render('admin/bibliotheque.html.twig', [
            'bibliotheque' => $bibliotheque,
        ]);
    }

    // Gestion Auteurs
    #[Route('/auteurs', name: 'admin_auteurs')]
    public function auteurs(AuteurRepository $auteurRepository): Response
    {
        $auteurs = $auteurRepository->findAll();

        return $this->render('admin/auteurs.html.twig', [
            'auteurs' => $auteurs,
        ]);
    }

    #[Route('/auteurs/new', name: 'admin_auteur_new')]
    public function newAuteur(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $auteur = new Auteur();
        $form = $this->createForm(AuteurType::class, $auteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($auteur);
            $entityManager->flush();

            $this->addFlash('success', 'Auteur créé avec succès');
            return $this->redirectToRoute('admin_auteurs');
        }

        return $this->render('admin/auteur_form.html.twig', [
            'form' => $form,
            'auteur' => $auteur,
        ]);
    }

    #[Route('/auteurs/{id}/edit', name: 'admin_auteur_edit')]
    public function editAuteur(
        Auteur $auteur,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(AuteurType::class, $auteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Auteur modifié avec succès');
            return $this->redirectToRoute('admin_auteurs');
        }

        return $this->render('admin/auteur_form.html.twig', [
            'form' => $form,
            'auteur' => $auteur,
        ]);
    }

    #[Route('/auteurs/{id}/delete', name: 'admin_auteur_delete', methods: ['POST'])]
    public function deleteAuteur(
        Auteur $auteur,
        EntityManagerInterface $entityManager
    ): Response {
        $entityManager->remove($auteur);
        $entityManager->flush();

        $this->addFlash('success', 'Auteur supprimé avec succès');
        return $this->redirectToRoute('admin_auteurs');
    }

    // Gestion Livres
    #[Route('/livres', name: 'admin_livres')]
    public function livres(LivreRepository $livreRepository): Response
    {
        $user = $this->getUser();
        $bibliotheque = $user->getBibliotheque();
        
        $livres = $bibliotheque 
            ? $livreRepository->findBy(['bibliotheque' => $bibliotheque])
            : [];

        return $this->render('admin/livres.html.twig', [
            'livres' => $livres,
        ]);
    }

    #[Route('/livres/new', name: 'admin_livre_new')]
    public function newLivre(
        Request $request,
        EntityManagerInterface $entityManager,
        AuteurRepository $auteurRepository,
        CategorieRepository $categorieRepository,
        EditeurRepository $editeurRepository
    ): Response {
        $livre = new Livre();
        $user = $this->getUser();
        $livre->setBibliotheque($user->getBibliotheque());
        
        $form = $this->createForm(LivreType::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($livre);
            $entityManager->flush();

            $this->addFlash('success', 'Livre créé avec succès');
            return $this->redirectToRoute('admin_livres');
        }

        return $this->render('admin/livre_form.html.twig', [
            'form' => $form,
            'livre' => $livre,
        ]);
    }

    #[Route('/livres/{id}/edit', name: 'admin_livre_edit')]
    public function editLivre(
        Livre $livre,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(LivreType::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Livre modifié avec succès');
            return $this->redirectToRoute('admin_livres');
        }

        return $this->render('admin/livre_form.html.twig', [
            'form' => $form,
            'livre' => $livre,
        ]);
    }

    #[Route('/livres/{id}/delete', name: 'admin_livre_delete', methods: ['POST'])]
    public function deleteLivre(
        Livre $livre,
        EntityManagerInterface $entityManager
    ): Response {
        $entityManager->remove($livre);
        $entityManager->flush();

        $this->addFlash('success', 'Livre supprimé avec succès');
        return $this->redirectToRoute('admin_livres');
    }

    // Gestion Catégories
    #[Route('/categories', name: 'admin_categories')]
    public function categories(CategorieRepository $categorieRepository): Response
    {
        $categories = $categorieRepository->findAll();

        return $this->render('admin/categories.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/categories/new', name: 'admin_categorie_new')]
    public function newCategorie(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($categorie);
            $entityManager->flush();

            $this->addFlash('success', 'Catégorie créée avec succès');
            return $this->redirectToRoute('admin_categories');
        }

        return $this->render('admin/categorie_form.html.twig', [
            'form' => $form,
            'categorie' => $categorie,
        ]);
    }

    #[Route('/categories/{id}/edit', name: 'admin_categorie_edit')]
    public function editCategorie(
        Categorie $categorie,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Catégorie modifiée avec succès');
            return $this->redirectToRoute('admin_categories');
        }

        return $this->render('admin/categorie_form.html.twig', [
            'form' => $form,
            'categorie' => $categorie,
        ]);
    }

    #[Route('/categories/{id}/delete', name: 'admin_categorie_delete', methods: ['POST'])]
    public function deleteCategorie(
        Categorie $categorie,
        EntityManagerInterface $entityManager
    ): Response {
        $entityManager->remove($categorie);
        $entityManager->flush();

        $this->addFlash('success', 'Catégorie supprimée avec succès');
        return $this->redirectToRoute('admin_categories');
    }

    // Gestion Éditeurs
    #[Route('/editeurs', name: 'admin_editeurs')]
    public function editeurs(EditeurRepository $editeurRepository): Response
    {
        $editeurs = $editeurRepository->findAll();

        return $this->render('admin/editeurs.html.twig', [
            'editeurs' => $editeurs,
        ]);
    }

    #[Route('/editeurs/new', name: 'admin_editeur_new')]
    public function newEditeur(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $editeur = new Editeur();
        $form = $this->createForm(EditeurType::class, $editeur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($editeur);
            $entityManager->flush();

            $this->addFlash('success', 'Éditeur créé avec succès');
            return $this->redirectToRoute('admin_editeurs');
        }

        return $this->render('admin/editeur_form.html.twig', [
            'form' => $form,
            'editeur' => $editeur,
        ]);
    }

    #[Route('/editeurs/{id}/edit', name: 'admin_editeur_edit')]
    public function editEditeur(
        Editeur $editeur,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(EditeurType::class, $editeur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Éditeur modifié avec succès');
            return $this->redirectToRoute('admin_editeurs');
        }

        return $this->render('admin/editeur_form.html.twig', [
            'form' => $form,
            'editeur' => $editeur,
        ]);
    }

    #[Route('/editeurs/{id}/delete', name: 'admin_editeur_delete', methods: ['POST'])]
    public function deleteEditeur(
        Editeur $editeur,
        EntityManagerInterface $entityManager
    ): Response {
        $entityManager->remove($editeur);
        $entityManager->flush();

        $this->addFlash('success', 'Éditeur supprimé avec succès');
        return $this->redirectToRoute('admin_editeurs');
    }
}

