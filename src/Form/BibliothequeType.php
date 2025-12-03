<?php

namespace App\Form;

use App\Entity\Bibliotheque;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BibliothequeType extends AbstractType
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('adresse')
            ->add('ville')
            ->add('telephone', null, ['required' => false])
            // Admin responsable (champ non mappé sur l'entité, géré dans le contrôleur)
            ->add('admin', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'label' => 'Administrateur associé',
                'required' => false,
                'mapped' => false,
                'placeholder' => 'Aucun administrateur',
                'help' => 'Optionnel : sélectionnez l\'admin responsable de cette bibliothèque',
                // Ne proposer que les utilisateurs ayant le rôle ROLE_ADMIN
                // (recherche textuelle simple dans la colonne JSON / texte des rôles)
                'query_builder' => function () {
                    return $this->userRepository
                        ->createQueryBuilder('u')
                        ->where('u.roles LIKE :role')
                        ->setParameter('role', '%ROLE_ADMIN%');
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Bibliotheque::class,
        ]);
    }
}