<?php

namespace App\Form;

use App\Entity\Commande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité',
                'attr' => ['min' => 1]
            ])
        ;
        
        // Le statut n'est ajouté que si l'option 'allow_status' est true (pour les admins)
        if ($options['allow_status'] ?? false) {
            $builder->add('statut', ChoiceType::class, [
                'choices' => [
                    'En attente' => 'en_attente',
                    'Confirmée' => 'confirmee',
                    'Annulée' => 'annulee',
                ],
                'label' => 'Statut'
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
            'allow_status' => false,
        ]);
    }
}

