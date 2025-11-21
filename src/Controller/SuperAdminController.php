<?php

namespace App\Controller;

use App\Entity\Bibliotheque;
use App\Entity\User;
use App\Form\BibliothequeType;
use App\Form\UserType;
use App\Repository\BibliothequeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/superadmin')]
#[IsGranted('ROLE_SUPER_ADMIN')]
class SuperAdminController extends AbstractController
{
    #[Route('/dashboard', name: 'superadmin_dashboard')]
    public function dashboard(
        UserRepository $userRepository,
        BibliothequeRepository $bibliothequeRepository
    ): Response {
        $totalUsers = $userRepository->count([]);
        $totalAdmins = $userRepository->count(['roles' => ['ROLE_ADMIN']]);
        $totalBibliotheques = $bibliothequeRepository->count([]);

        return $this->render('superadmin/dashboard.html.twig', [
            'totalUsers' => $totalUsers,
            'totalAdmins' => $totalAdmins,
            'totalBibliotheques' => $totalBibliotheques,
        ]);
    }

    #[Route('/users', name: 'superadmin_users')]
    public function users(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('superadmin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/users/new', name: 'superadmin_user_new')]
    public function newUser(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, [
            'require_password' => true,
            'include_bibliotheque' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                // Afficher les erreurs de validation
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            } else {
                $plainPassword = $form->get('password')->getData();
                if (!$plainPassword || empty(trim($plainPassword))) {
                    $this->addFlash('error', 'Le mot de passe est requis');
                } else {
                    $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                    $user->setPassword($hashedPassword);

                    // Les rôles sont déjà assignés par le formulaire
                    // On s'assure juste qu'ils sont bien un tableau
                    $roles = $form->get('roles')->getData();
                    if (is_array($roles)) {
                        // Retirer ROLE_USER s'il est présent car il est ajouté automatiquement
                        $roles = array_filter($roles, fn($role) => $role !== 'ROLE_USER');
                        $user->setRoles(array_values($roles));
                    }

                    try {
                        $entityManager->persist($user);
                        $entityManager->flush();

                        $this->addFlash('success', 'Utilisateur créé avec succès');
                        return $this->redirectToRoute('superadmin_users');
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Erreur lors de la création : ' . $e->getMessage());
                    }
                }
            }
        }

        return $this->render('superadmin/user_form.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/users/{id}/edit', name: 'superadmin_user_edit')]
    public function editUser(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $form = $this->createForm(UserType::class, $user, [
            'require_password' => false,
            'include_bibliotheque' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            // Les rôles sont déjà assignés par le formulaire
            // On s'assure juste qu'ils sont bien un tableau
            $roles = $form->get('roles')->getData();
            if (is_array($roles)) {
                // Retirer ROLE_USER s'il est présent car il est ajouté automatiquement
                $roles = array_filter($roles, fn($role) => $role !== 'ROLE_USER');
                $user->setRoles(array_values($roles));
            }

            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès');
            return $this->redirectToRoute('superadmin_users');
        }

        return $this->render('superadmin/user_form.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/users/{id}/delete', name: 'superadmin_user_delete', methods: ['POST'])]
    public function deleteUser(User $user, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès');
        return $this->redirectToRoute('superadmin_users');
    }

    #[Route('/bibliotheques', name: 'superadmin_bibliotheques')]
    public function bibliotheques(BibliothequeRepository $bibliothequeRepository): Response
    {
        $bibliotheques = $bibliothequeRepository->findAll();

        return $this->render('superadmin/bibliotheques.html.twig', [
            'bibliotheques' => $bibliotheques,
        ]);
    }

    #[Route('/bibliotheques/new', name: 'superadmin_bibliotheque_new')]
    public function newBibliotheque(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $bibliotheque = new Bibliotheque();
        $form = $this->createForm(BibliothequeType::class, $bibliotheque);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($bibliotheque);
            $entityManager->flush();

            $this->addFlash('success', 'Bibliothèque créée avec succès');
            return $this->redirectToRoute('superadmin_bibliotheques');
        }

        return $this->render('superadmin/bibliotheque_form.html.twig', [
            'form' => $form,
            'bibliotheque' => $bibliotheque,
        ]);
    }

    #[Route('/bibliotheques/{id}/edit', name: 'superadmin_bibliotheque_edit')]
    public function editBibliotheque(
        Bibliotheque $bibliotheque,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(BibliothequeType::class, $bibliotheque);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Bibliothèque modifiée avec succès');
            return $this->redirectToRoute('superadmin_bibliotheques');
        }

        return $this->render('superadmin/bibliotheque_form.html.twig', [
            'form' => $form,
            'bibliotheque' => $bibliotheque,
        ]);
    }

    #[Route('/bibliotheques/{id}/delete', name: 'superadmin_bibliotheque_delete', methods: ['POST'])]
    public function deleteBibliotheque(
        Bibliotheque $bibliotheque,
        EntityManagerInterface $entityManager
    ): Response {
        $entityManager->remove($bibliotheque);
        $entityManager->flush();

        $this->addFlash('success', 'Bibliothèque supprimée avec succès');
        return $this->redirectToRoute('superadmin_bibliotheques');
    }
}
