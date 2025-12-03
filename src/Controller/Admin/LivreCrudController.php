<?php

namespace App\Controller\Admin;

use App\Entity\Livre;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class LivreCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Livre::class;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        
        $user = $this->getUser();
        
        // Filtrer pour que chaque admin ne voie que les livres de sa bibliothèque
        if ($user instanceof User && $user->getBibliotheque() !== null) {
            $qb->andWhere('entity.bibliotheque = :bibliotheque')
                ->setParameter('bibliotheque', $user->getBibliotheque());
        }
        
        return $qb;
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
            AssociationField::new('bibliotheque')
                ->setLabel('Bibliothèque')
                ->hideOnForm()
                ->formatValue(function ($value, $entity) {
                    return $entity?->getBibliotheque()?->getNom() ?? 'Non assignée';
                }),
            AssociationField::new('auteur')->setLabel('Auteur'),
            AssociationField::new('categorie')->setLabel('Catégorie'),
            AssociationField::new('editeur')->setLabel('Éditeur'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Livre) {
            $user = $this->getUser();

            // Assigner automatiquement la bibliothèque de l'admin connecté
            if ($user instanceof User && $user->getBibliotheque() !== null) {
                $entityInstance->setBibliotheque($user->getBibliotheque());
            }
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Livre) {
            $user = $this->getUser();

            // S'assurer que le livre reste assigné à la bibliothèque de l'admin (sécurité)
            if ($user instanceof User && $user->getBibliotheque() !== null) {
                // Un admin ne peut modifier que les livres de sa bibliothèque
                $currentBibliotheque = $entityInstance->getBibliotheque();
                $adminBibliotheque = $user->getBibliotheque();
                
                // Si le livre n'a pas de bibliothèque ou si ce n'est pas celle de l'admin, on la corrige
                if ($currentBibliotheque === null || $currentBibliotheque->getId() !== $adminBibliotheque->getId()) {
                    $entityInstance->setBibliotheque($adminBibliotheque);
                }
            }
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
