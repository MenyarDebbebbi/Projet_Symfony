<?php

namespace App\Controller;

use App\Entity\Editeur;
use App\Form\EditeurType;
use App\Repository\EditeurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/editeur')]
final class EditeurController extends AbstractController
{
    public function recherche() {

        
    }
    #[Route(name: 'app_editeur_index', methods: ['GET'])]
    public function index(Request $request, EditeurRepository $editeurRepository): Response
    {
        $searchTerm = $request->query->get('search', '');
        $searchType = $request->query->get('type', 'all');

        if ($searchTerm) {
            $editeurs = $editeurRepository->findBySearchCriteria($searchTerm, $searchType);
        } else {
            $editeurs = $editeurRepository->findAll();
        }

        return $this->render('editeur/index.html.twig', [
            'editeurs' => $editeurs,
            'searchTerm' => $searchTerm,
            'searchType' => $searchType,
        ]);
    }

    #[Route('/new', name: 'app_editeur_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $editeur = new Editeur();
        $form = $this->createForm(EditeurType::class, $editeur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($editeur);
            $entityManager->flush();

            return $this->redirectToRoute('app_editeur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('editeur/new.html.twig', [
            'editeur' => $editeur,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_editeur_show', methods: ['GET'])]
    public function show(Editeur $editeur): Response
    {
        return $this->render('editeur/show.html.twig', [
            'editeur' => $editeur,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_editeur_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Editeur $editeur, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EditeurType::class, $editeur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_editeur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('editeur/edit.html.twig', [
            'editeur' => $editeur,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_editeur_delete', methods: ['POST'])]
    public function delete(Request $request, Editeur $editeur, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $editeur->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($editeur);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_editeur_index', [], Response::HTTP_SEE_OTHER);
    }
}
