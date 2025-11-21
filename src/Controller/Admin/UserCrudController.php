<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            EmailField::new('email'),
            TextField::new('password')
                ->setLabel('Mot de passe')
                ->hideOnIndex()
                ->setRequired($pageName === 'new')
                ->setHelp('Laissez vide pour ne pas modifier le mot de passe'),
            ArrayField::new('roles')
                ->setLabel('Rôles')
                ->setHelp('Exemple: ROLE_ADMIN, ROLE_USER'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var User $entityInstance */
        if ($entityInstance->getPassword()) {
            $hashedPassword = $this->passwordHasher->hashPassword(
                $entityInstance,
                $entityInstance->getPassword()
            );
            $entityInstance->setPassword($hashedPassword);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var User $entityInstance */
        $plainPassword = $entityInstance->getPassword();

        // Si un nouveau mot de passe est fourni, le hasher
        if (!empty($plainPassword)) {
            $hashedPassword = $this->passwordHasher->hashPassword(
                $entityInstance,
                $plainPassword
            );
            $entityInstance->setPassword($hashedPassword);
        } else {
            // Sinon, récupérer le mot de passe existant depuis la base de données
            $existingUser = $entityManager->getRepository(User::class)->find($entityInstance->getId());
            if ($existingUser) {
                $entityInstance->setPassword($existingUser->getPassword());
            }
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}