<?php

namespace App\Controller\Admin;

use App\Entity\Livre;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class LivreCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Livre::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('titre'),
            ImageField::new('imageName')
                ->setLabel('Image')
                ->setBasePath('/uploads/livres')
                ->hideOnForm(),
            IntegerField::new('qte')->setLabel('Quantité'),
            TextField::new('priorite')->setLabel('Priorité'),
            TextField::new('isbn')->setLabel('ISBN'),
            DateField::new('datepub')->setLabel('Date de publication'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Livre) {
            $user = $this->getUser();

            // Si aucune bibliothèque n'est choisie dans le formulaire EasyAdmin,
            // on rattache automatiquement le livre à la bibliothèque de l'admin connecté (si définie).
            if ($user instanceof User && $entityInstance->getBibliotheque() === null && $user->getBibliotheque() !== null) {
                $entityInstance->setBibliotheque($user->getBibliotheque());
            }
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Livre) {
            $user = $this->getUser();

            if ($user instanceof User && $entityInstance->getBibliotheque() === null && $user->getBibliotheque() !== null) {
                $entityInstance->setBibliotheque($user->getBibliotheque());
            }
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
