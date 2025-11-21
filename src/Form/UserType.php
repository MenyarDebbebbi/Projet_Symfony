<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Bibliotheque;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email'
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false,
                'required' => $options['require_password'],
                'help' => $options['require_password'] ? '' : 'Laissez vide pour ne pas modifier le mot de passe'
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôles',
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                    'Super Administrateur' => 'ROLE_SUPER_ADMIN',
                ],
                'multiple' => true,
                'expanded' => false,
                'help' => 'Sélectionnez un ou plusieurs rôles'
            ])
        ;

        // Ajouter le champ bibliothèque seulement si l'option est activée
        if ($options['include_bibliotheque']) {
            $builder->add('bibliotheque', EntityType::class, [
                'class' => Bibliotheque::class,
                'choice_label' => 'nom',
                'label' => 'Bibliothèque',
                'required' => false,
                'placeholder' => 'Aucune bibliothèque'
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'require_password' => true,
            'include_bibliotheque' => false,
        ]);
    }
}

