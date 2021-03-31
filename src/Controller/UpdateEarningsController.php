<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UpdateEarningsController extends AbstractController
{
    // Fonction qui permet de récupérer les informations qui nous intéresse pour chacune des transactions, dans l'objectif final de retourner le gain généré par chacune de nos trois crypto monnaies
    // Cette fonction est appelée par la page affichant le graphique d'évolutions des gains
    public function updateCurrentEarningsPerCrypto($evols, $transactions)
    {
        $price_Bitcoin = 0;
        $price_Ethereum = 0;
        $price_XRP = 0;

        for ($i = 0; $i < count($evols); $i++) {
            $nameCrypto = $evols[$i]->getNameOfCrypto();
            switch ($nameCrypto) {
                case 'BTC':
                    $price_Bitcoin = $evols[$i]->getPriceEvolutionOfCrypto();
                    break;
                case 'ETH':
                    $price_Ethereum = $evols[$i]->getPriceEvolutionOfCrypto();
                    break;
                case 'XRP':
                    $price_XRP = $evols[$i]->getPriceEvolutionOfCrypto();
                    break;
            }
        }

        // Initalisation des tableaux pour chacune des crypto monnaies, permettant de récupérer le montant et la quantité de chacune des transactions
        $quantityBitcoin = array();
        $unitAmountBitcoin = array();

        $quantityEthereum = array();
        $unitAmountEthereum = array();

        $quantityXRP = array();
        $unitAmountXRP = array();

        //Pour chacun des transactions, récupération de la quantité et du montant au moment de la transaction
        foreach ($transactions as $transaction) {
            $nameCrypto = $transaction->getNameCrypto();
            $quantityCrypto = $transaction->getQuantity();
            $unitAmountCrypto = $transaction->getUnitAmount();
            switch ($nameCrypto) {
                case 'Bitcoin':
                    array_push($quantityBitcoin, $quantityCrypto);
                    array_push($unitAmountBitcoin, $unitAmountCrypto);
                    break;
                case 'Ethereum':
                    array_push($quantityEthereum, $quantityCrypto);
                    array_push($unitAmountEthereum, $unitAmountCrypto);
                    break;
                case 'XRP':
                    array_push($quantityXRP, $quantityCrypto);
                    array_push($unitAmountXRP, $unitAmountCrypto);
                    break;
            }
        }

        $updateEarnings = new UpdateEarningsController();

        $earningsBitcoin = $updateEarnings->countEarningsPerCrypto($quantityBitcoin, $unitAmountBitcoin, $price_Bitcoin);
        $earningsEthereum = $updateEarnings->countEarningsPerCrypto($quantityEthereum, $unitAmountEthereum, $price_Ethereum);
        $earningsXRP = $updateEarnings->countEarningsPerCrypto($quantityXRP, $unitAmountXRP, $price_XRP);

        $updateEarnings = array($earningsBitcoin, $earningsEthereum, $earningsXRP);
        return ($updateEarnings);
    }

    // Fonction qui permet de compter les gains pour chacune des crypto monnaies, sans être obligé d'écrire le code pour chacune de nos 3 crypto monnaies
    public function countEarningsPerCrypto($quantityCrypto, $unitAmountCrypto, $price_crypto)
    {
        if (empty($quantityCrypto)) {
            $earningsCrypto = 0;
            $quantityOfCrypto = 0;
            $averagePrice = 0;
        } else {
            $quantityOfCrypto = 0;
            foreach ($quantityCrypto as $quantity) {
                $quantityOfCrypto += $quantity;
            }
            $sumUnitAmountMultipliedByQuantity = 0;
            for ($i = 0; $i < count($unitAmountCrypto); $i++) {
                $unitAmountMultipliedByQuantity = $unitAmountCrypto[$i] * $quantityCrypto[$i];
                $sumUnitAmountMultipliedByQuantity += $unitAmountMultipliedByQuantity;
            }
            $averagePrice = $sumUnitAmountMultipliedByQuantity / $quantityOfCrypto;
            $earningsCrypto = ($quantityOfCrypto * $price_crypto) - ($quantityOfCrypto * $averagePrice);
        }
        $currentStateCryptoEarnings = array($quantityOfCrypto, $averagePrice, $earningsCrypto);
        return $currentStateCryptoEarnings;
    }
}
