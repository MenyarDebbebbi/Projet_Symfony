<?php

namespace App\Form;

use App\Entity\Livre;
use App\Entity\Auteur;
use App\Entity\Categorie;
use App\Entity\Editeur;
use App\Entity\Bibliotheque;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LivreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('isbn', null, [
                'label' => 'ISBN',
                'required' => false
            ])
            ->add('qte', IntegerType::class, [
                'label' => 'Quantité'
            ])
            ->add('priorite', ChoiceType::class, [
                'choices' => [
                    'Haute' => 'Haute',
                    'Moyenne' => 'Moyenne',
                    'Basse' => 'Basse',
                ],
                'label' => 'Priorité'
            ])
            ->add('datepub', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de publication'
            ])
            ->add('bibliotheque', EntityType::class, [
                'class' => Bibliotheque::class,
                'choice_label' => 'nom',
                'label' => 'Bibliothèque',
                'required' => false
            ])
            ->add('auteur', EntityType::class, [
                'class' => Auteur::class,
                'choice_label' => function(Auteur $auteur) {
                    return $auteur->getNom() . ' ' . $auteur->getPrenom();
                },
                'label' => 'Auteur',
                'required' => false
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'designation',
                'label' => 'Catégorie',
                'required' => false
            ])
            ->add('editeur', EntityType::class, [
                'class' => Editeur::class,
                'choice_label' => 'nom',
                'label' => 'Éditeur',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Livre::class,
        ]);
    }
}
