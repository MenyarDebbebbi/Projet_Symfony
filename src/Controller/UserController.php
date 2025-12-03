<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Livre;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use App\Repository\LivreRepository;
use App\Repository\BibliothequeRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user')]
#[IsGranted('ROLE_USER')]
class UserController extends AbstractController
{
    #[Route('/dashboard', name: 'user_dashboard')]
    public function dashboard(
        Request $request,
        CommandeRepository $commandeRepository,
        LivreRepository $livreRepository,
        BibliothequeRepository $bibliothequeRepository,
        CategorieRepository $categorieRepository
    ): Response {
        $user = $this->getUser();
        $commandes = $commandeRepository->findByUser($user);
        
        // Récupérer les paramètres de filtre
        $bibliothequeId = $request->query->getInt('bibliotheque', 0) ?: null;
        $categorieId = $request->query->getInt('categorie', 0) ?: null;

        // Récupérer les livres filtrés
        $livres = $livreRepository->findFiltered($bibliothequeId, $categorieId);

        // Récupérer toutes les bibliothèques et catégories pour les filtres
        $bibliotheques = $bibliothequeRepository->findAll();
        $categories = $categorieRepository->findAll();

        return $this->render('user/dashboard.html.twig', [
            'commandes' => $commandes,
            'livres' => $livres,
            'bibliotheques' => $bibliotheques,
            'categories' => $categories,
            'selectedBibliotheque' => $bibliothequeId,
            'selectedCategorie' => $categorieId,
        ]);
    }

    #[Route('/livres', name: 'user_livres')]
    public function livres(
        Request $request,
        LivreRepository $livreRepository,
        BibliothequeRepository $bibliothequeRepository,
        CategorieRepository $categorieRepository
    ): Response {
        // Récupérer les paramètres de filtre
        $bibliothequeId = $request->query->getInt('bibliotheque', 0) ?: null;
        $categorieId = $request->query->getInt('categorie', 0) ?: null;

        // Récupérer les livres filtrés
        $livres = $livreRepository->findFiltered($bibliothequeId, $categorieId);

        // Récupérer toutes les bibliothèques et catégories pour les filtres
        $bibliotheques = $bibliothequeRepository->findAll();
        $categories = $categorieRepository->findAll();

        return $this->render('user/livres.html.twig', [
            'livres' => $livres,
            'bibliotheques' => $bibliotheques,
            'categories' => $categories,
            'selectedBibliotheque' => $bibliothequeId,
            'selectedCategorie' => $categorieId,
        ]);
    }

    #[Route('/livres/{id}', name: 'user_livre_show')]
    public function showLivre(int $id, LivreRepository $livreRepository): Response
    {
        $livre = $livreRepository->findOneWithRelations($id);
        
        if (!$livre) {
            throw $this->createNotFoundException('Livre non trouvé');
        }

        return $this->render('user/livre_show.html.twig', [
            'livre' => $livre,
        ]);
    }

    #[Route('/livres/{id}/commander', name: 'user_commander')]
    public function commander(
        int $id,
        Request $request,
        LivreRepository $livreRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $livre = $livreRepository->findOneWithRelations($id);
        
        if (!$livre) {
            throw $this->createNotFoundException('Livre non trouvé');
        }
        
        $user = $this->getUser();

        $commande = new Commande();
        $commande->setUser($user);
        $commande->setLivre($livre);
        $commande->setQuantite(1);

        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($commande->getQuantite() > $livre->getQte()) {
                $this->addFlash('error', 'Quantité demandée supérieure au stock disponible');
                return $this->redirectToRoute('user_livre_show', ['id' => $livre->getId()]);
            }

            $entityManager->persist($commande);
            $entityManager->flush();

            $this->addFlash('success', 'Commande effectuée avec succès');
            return $this->redirectToRoute('user_commandes');
        }

        return $this->render('user/commander.html.twig', [
            'form' => $form,
            'livre' => $livre,
            'commande' => $commande,
        ]);
    }

    #[Route('/commandes', name: 'user_commandes')]
    public function commandes(CommandeRepository $commandeRepository): Response
    {
        $user = $this->getUser();
        $commandes = $commandeRepository->findByUser($user);

        return $this->render('user/commandes.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    #[Route('/commandes/{id}', name: 'user_commande_show')]
    public function showCommande(Commande $commande): Response
    {
        $user = $this->getUser();

        if ($commande->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('user/commande_show.html.twig', [
            'commande' => $commande,
        ]);
    }
}
