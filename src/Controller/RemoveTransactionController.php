<?php

namespace App\Controller;

use App\Entity\Transaction;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Form\RemoveTransactionForm;
use DateTime;

class RemoveTransactionController extends AbstractController
{
    // Fonction pour afficher le formulaire de suppression d'un montant et gérer la mise en base de données des informations renseignées si tout est correct
    public function displayRemoveTransactionPage(Request $request)
    {
        // Création du formulaire à partir de la classe "RemoveTransactionForm"
        $messageOfConfirmation = '';
        $form = $this->createForm(RemoveTransactionForm::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $data = $form->getData();
            // Récupération des transactions faites par le passé, pour vérifier que l'on a assez de crypto monnaies pour en vendre
            $entityManager = $this->getDoctrine()->getManager();
            $repository = $entityManager->getRepository(Transaction::class);
            $transactions = $repository->findAll();

            $sumOfQuantityCrypto = 0;

            foreach ($transactions as $transaction) {
                $nameCrypto = $transaction->getNameCrypto();
                if ($nameCrypto = $data['selectcrypto']) {
                    $quantityCrypto = $transaction->getQuantity();
                    $sumOfQuantityCrypto += $quantityCrypto;
                }
            }

            // Appel à l'API, pour récupérer le montant de la crypto monnaie pour laquelle on souhaite supprimer un montant
            $apiController = new APIController();
            $parsed_json = $apiController->callAPI();

            // On stocke temporairement la valeur de chacun des crypto monnaies
            for ($i = 0; $i < 10; $i++) {
                $varTempNameCrypto = $parsed_json->{'data'}[$i]->{'name'};
                if ($varTempNameCrypto == $data['selectcrypto']) {
                    $nameCrypto = $varTempNameCrypto;
                    $priceCrypto = $parsed_json->{'data'}[$i]->{'quote'}->{'EUR'}->{'price'};
                }
            }

            // On vérifie si la monnaie indiquée dans le formulaire correspond bien soit à Bitcoin, Ethereum ou XRP
            if (in_array($data['selectcrypto'], ['Bitcoin', 'Ethereum', 'XRP'])) {
                // On vérifie que la quantité indiquée est bien un nombre, qui peut être à virgule
                if (array_key_exists('quantity', $data)) {
                    if (is_numeric($data['quantity'])) {
                        // Si tout est OK, on initialise les données à entrer en base de données
                        $nameCrypto = $data['selectcrypto'];
                        $currentTime = new DateTime('now');
                        $quantity = -$data['quantity'];
                        // On vérifie que la quantité que l'on possède est suffisante pour la vente
                        if (($sumOfQuantityCrypto + $quantity) < 0) {
                            // Affichage à l'utilisateur d'une erreur concernant la quantité qu'il cherche à vendre, qui n'est pas disponible
                            $messageOfConfirmation = "Vous chercher à vendre plus de crypto-monnaie que vous en avez. Veuillez revoir la quantité.";
                        } else {
                            //Initalisation de la transaction à ajouter en base de données
                            $newTransaction = new Transaction($nameCrypto, $currentTime, $quantity, $priceCrypto);

                            // Enregistrement de la transaction en base de données   
                            $entityManager = $this->getDoctrine()->getManager();
                            $entityManager->persist($newTransaction);
                            $entityManager->flush();

                            // Affichage à l'utilisateur de la confirmation que la transaction a bien été ajoutée en base de données
                            $messageOfConfirmation = "Votre suppression a bien été prise en compte.";
                        }
                    } else {
                        // Affichage à l'utilisateur d'une erreur concernant la quantité
                        $messageOfConfirmation = "Vous devez indiquer un nombre dans le champ quantité.";
                    }
                } else {
                    // Affichage à l'utilisateur d'une erreur concernant la quantité
                    $messageOfConfirmation = "Vous devez indiquer un nombre dans le champ quantité.";
                }
            } else {
                // Affichage à l'utilisateur d'une erreur concernant la sélection de la crypto monnaie
                $messageOfConfirmation = "Vous devez sélectionner une crypto-monnaie parmi la liste affichée, à savoir : Bitcoin, Ethereum et XRP.";
            }
        }

        return $this->render('removeTransaction.html.twig', array('form' => $form->createView(), 'confirmation' => $messageOfConfirmation));
    }
}
