<?php

namespace App\Controller;

use App\Entity\Transaction;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Form\AddNewTransactionForm;
use DateTime;

class AddNewTransactionController extends AbstractController
{
    // Fonction pour afficher le formulaire d'ajout d'une nouvelle transaction et gérer la mise en base de données des informations renseignées si tout est correct
    public function displayAddNewTransactionPage(Request $request)
    {
        // Création du formulaire à partir de la classe "AddNewTransactionForm"
        $messageOfConfirmation = '';
        $form = $this->createForm(AddNewTransactionForm::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $data = $form->getData();
            // On vérifie si la monnaie indiquée dans le formulaire correspond bien soit à Bitcoin, Ethereum ou XRP
            if (in_array($data['selectcrypto'], ['Bitcoin', 'Ethereum', 'XRP'])) {
                // On vérifie que la quantité indiquée est bien un nombre, qui peut être à virgule
                if (array_key_exists('quantity', $data)) {
                    if (is_numeric($data['quantity'])) {
                        // On vérifie que le montant indiqué est bien un nombre, qui peut être à virgule
                        if (array_key_exists('price', $data)) {
                            if (is_numeric($data['price'])) {
                                // Si tout est OK, on initialise les données à entrer en base de données
                                $nameCrypto = $data['selectcrypto'];
                                $currentTime = new DateTime('now');
                                $quantity = $data['quantity'];
                                $unitAmount = $data['price'];
                                $newTransaction = new Transaction($nameCrypto, $currentTime, $quantity, $unitAmount);

                                // Enregistrement de la transaction en base de données
                                $entityManager = $this->getDoctrine()->getManager();
                                $entityManager->persist($newTransaction);
                                $entityManager->flush();

                                // Affichage à l'utilisateur de la confirmation que la transaction a bien été ajoutée en base de données
                                $messageOfConfirmation = "Votre nouvelle transaction a bien été ajoutée en base de données.";
                            } else {
                                // Affichage à l'utilisateur d'une erreur concernant le champ du prix d'achat
                                $messageOfConfirmation = "Vous devez indiquer un nombre pour le prix d'achat.";
                            }
                        } else {
                            // Affichage à l'utilisateur d'une erreur concernant le champ du prix d'achat
                            $messageOfConfirmation = "Vous devez indiquer un nombre pour le prix d'achat.";
                        }
                    } else {
                        // Affichage à l'utilisateur d'une erreur concernant la quantité
                        $messageOfConfirmation = "Vous devez indiquer un nombre pour la quantité de la transaction.";
                    }
                } else {
                    // Affichage à l'utilisateur d'une erreur concernant la quantité
                    $messageOfConfirmation = "Vous devez indiquer un nombre pour la quantité de la transaction.";
                }
            } else {
                // Affichage à l'utilisateur d'une erreur concernant la sélection de la crypto monnaie
                $messageOfConfirmation = "Vous devez sélectionner une crypto-monnaie parmi la liste affichée, à savoir : Bitcoin, Ethereum et XRP.";
            }
        }

        return $this->render('addNewTransaction.html.twig', array('form' => $form->createView(), 'confirmation' => $messageOfConfirmation));
    }
}
