<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Livre;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use App\Repository\LivreRepository;
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
        CommandeRepository $commandeRepository,
        LivreRepository $livreRepository
    ): Response {
        $user = $this->getUser();
        $commandes = $commandeRepository->findByUser($user);
        $livres = $livreRepository->findAll();

        return $this->render('user/dashboard.html.twig', [
            'commandes' => $commandes,
            'livres' => $livres,
        ]);
    }

    #[Route('/livres', name: 'user_livres')]
    public function livres(LivreRepository $livreRepository): Response
    {
        $livres = $livreRepository->findAll();

        return $this->render('user/livres.html.twig', [
            'livres' => $livres,
        ]);
    }

    #[Route('/livres/{id}', name: 'user_livre_show')]
    public function showLivre(Livre $livre): Response
    {
        return $this->render('user/livre_show.html.twig', [
            'livre' => $livre,
        ]);
    }

    #[Route('/livres/{id}/commander', name: 'user_commander')]
    public function commander(
        Livre $livre,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
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
