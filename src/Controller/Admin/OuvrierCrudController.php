<?php

namespace App\Controller\Admin;

use App\Entity\Ouvrier;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class OuvrierCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Ouvrier::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('nom'),
            TextField::new('prenom'),
            EmailField::new('mail')->setLabel('Email'),
            TextField::new('grade'),
        ];
    }
}

