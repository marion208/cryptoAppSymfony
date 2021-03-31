<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class AddNewTransactionForm extends AbstractType
{
    // Création du formulaire permettant d'ajouter une nouvelle transaction en base de données
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('selectcrypto', ChoiceType::class, [
                'choices' => [
                    'Bitcoin' => 'Bitcoin',
                    'Ethereum' => 'Ethereum',
                    'XRP' => 'XRP'
                ],
                'placeholder' => 'Sélectionner une crypto'
            ])
            ->add('quantity', NumberType::class, ['attr' => ['placeholder' => 'Quantité']])
            ->add('price', NumberType::class, ['attr' => ['placeholder' => 'Prix d\'achat']]);
    }
}
