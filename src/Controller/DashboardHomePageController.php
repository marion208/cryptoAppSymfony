<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Controller\APIController;
use App\Entity\EvolutionCryptos;
use App\Entity\Transaction;
use DateTime;

class DashboardHomePageController extends AbstractController
{
    // Fonction permettant d'afficher la page d'accueil avec les informations du tableau de bord
    public function displayDashboardHomePage()
    {
        // Récupération des dernières données de gains pour chacune des cryptos monnaies
        $entityManager = $this->getDoctrine()->getManager();
        $query = $entityManager->createQuery('SELECT c FROM App:EvolutionCryptos c ORDER BY c.id DESC');
        $query->setMaxResults(3);
        $evols = $query->getResult();

        // Si les dernières données ne sont pas du jour-même, on met à jour ces données
        $currentTime = new DateTime('now');
        $dateOfCurrentTime = $currentTime->format('d-m-y');
        if (isset($evols[0])) {
            $referenceTimestamp = $evols[0]->getDateEvolutionOfCrypto();
        } else {
            $referenceTimestamp = 1;
        }
        $referenceDate = $referenceTimestamp->format('d-m-y');

        if ($dateOfCurrentTime != $referenceDate) {
            // Appel à l'API pour mettre à jour le tableau de bord pour l'évolution de chacun des crypto monnaies
            $apiController = new APIController();
            $parsed_json = $apiController->callAPI();

            // Initalisation de tableaux pour mettre à jour les montants des 3 crypto monnaies qui nous intéressent
            $namesCrypto = array();
            $shortnamesCrypto = array();
            $priceCrypto = array();

            $listOfOurNamesCrypto = array('Bitcoin', 'Ethereum', 'XRP');

            // Pour chacune des 10 crypto monnaies retournées, on vérifie si elles sont dans la liste qui nous intéresse et on insère les données dans nos tableaux
            for ($i = 0; $i < 10; $i++) {
                $varTempNameCrypto = $parsed_json->{'data'}[$i]->{'name'};
                if (in_array($varTempNameCrypto, $listOfOurNamesCrypto)) {
                    $namesCrypto[] = $varTempNameCrypto;
                    $shortnamesCrypto[] = $parsed_json->{'data'}[$i]->{'symbol'};
                    $priceCrypto[] = $parsed_json->{'data'}[$i]->{'quote'}->{'EUR'}->{'price'};
                }
            }

            // Initalisation de chacune des trois crypto monnaies, avec leur prix respectif issu de l'API
            for ($i = 0; $i < count($namesCrypto); $i++) {
                switch ($namesCrypto[$i]) {
                    case 'Bitcoin':
                        $nameCryptoBitcoin = $shortnamesCrypto[$i];
                        $priceCryptoBitcoin = $priceCrypto[$i];
                        break;
                    case 'Ethereum':
                        $nameCryptoEthereum = $shortnamesCrypto[$i];
                        $priceCryptoEthereum = $priceCrypto[$i];
                        break;
                    case 'XRP':
                        $nameCryptoXRP = $shortnamesCrypto[$i];
                        $priceCryptoXRP = $priceCrypto[$i];
                        break;
                }
            }

            $currentTime = new DateTime('now');

            // Récupération des dernières évolutions de prix de chacune des 3 crypto monnaies en base de données, pour mettre à jour les gains
            $entityManager = $this->getDoctrine()->getManager();
            $query = $entityManager->createQuery('SELECT c FROM App:EvolutionCryptos c ORDER BY c.id DESC');
            $query->setMaxResults(3);
            $evols = $query->getResult();

            // Récupération de toutes les transactions déjà faites
            $repository = $entityManager->getRepository(Transaction::class);
            $transactions = $repository->findAll();

            // Récupération des dernières évolutions de prix de chacune des 3 crypto monnaies en base de données, pour mettre à jour les gains
            $updateEarnings = new UpdateEarningsController();
            $updatedEarnings = $updateEarnings->updateCurrentEarningsPerCrypto($evols, $transactions);

            $quantityOfBitcoin = $updatedEarnings[0][0];
            $averagePriceBitcoin = $updatedEarnings[0][1];
            $earningsBitcoin = $updatedEarnings[0][2];
            $quantityOfEthereum = $updatedEarnings[1][0];
            $averagePriceEthereum = $updatedEarnings[1][1];
            $earningsEthereum = $updatedEarnings[1][2];
            $quantityOfXRP = $updatedEarnings[2][0];
            $averagePriceXRP = $updatedEarnings[2][1];
            $earningsXRP = $updatedEarnings[2][2];

            // Initalisation des évolutions à ajouter en base de données
            $evolution1 = new EvolutionCryptos($nameCryptoBitcoin, $priceCryptoBitcoin, $currentTime, $quantityOfBitcoin, $averagePriceBitcoin, $earningsBitcoin);
            $evolution2 = new EvolutionCryptos($nameCryptoEthereum, $priceCryptoEthereum, $currentTime, $quantityOfEthereum, $averagePriceEthereum, $earningsEthereum);
            $evolution3 = new EvolutionCryptos($nameCryptoXRP, $priceCryptoXRP, $currentTime, $quantityOfXRP, $averagePriceXRP, $earningsXRP);

            // Ajout en base de données des évolutions
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($evolution1);
            $entityManager->flush();

            $entityManager->persist($evolution2);
            $entityManager->flush();

            $entityManager->persist($evolution3);
            $entityManager->flush();
        }

        // On récupère les évolutions une nouvelle fois, au cas où elles ont été mise à jour
        $query = $entityManager->createQuery('SELECT c FROM App:EvolutionCryptos c ORDER BY c.id DESC');
        $query->setMaxResults(3);
        $evols = $query->getResult();

        $earningsBitcoin = 0;
        $earningsEthereum = 0;
        $earningsXRP = 0;

        foreach ($evols as $evol) {
            $nameCrypto = $evol->getNameOfCrypto();
            switch ($nameCrypto) {
                case 'BTC':
                    $earningsBitcoin = $evol->getEarnings();
                    break;
                case 'ETH':
                    $earningsEthereum = $evol->getEarnings();
                    break;
                case 'XRP':
                    $earningsXRP = $evol->getEarnings();
                    break;
            }
        }

        // Calcul du gain global pour affichage dans le tableau de bord de l'accueil
        $earnings = $earningsBitcoin + $earningsEthereum + $earningsXRP;
        if ($earnings > 0) {
            $globalEarnings = "+ " . round($earnings, 0, PHP_ROUND_HALF_EVEN) . " €";
        } elseif ($earnings < 0) {
            $globalEarnings = round($earnings, 0, PHP_ROUND_HALF_EVEN) . " €";
        } else {
            $globalEarnings = "- €";
        }

        // Appel à l'API pour mettre à jour le tableau de bord pour l'évolution du cours de chacun des crypto monnaies
        $apiController = new APIController();
        $parsed_json = $apiController->callAPI();

        // Pour chacune des 10 crypto monnaies extraites via l'API, on cherche les 3 crypto monnaies qui nous intéressent et on cherche l'évolution de leur valeur sur 24h, puis on initialise une variable permettant de montrer si l'on est dans une hausse / forte hausse / baisse / forte baisse
        $namesCrypto = array();
        $shortnamesCrypto = array();
        $pricesCrypto = array();
        $evols24hCrypto = array();
        $evols24hString = array();

        $listOfOurNamesCrypto = array('Bitcoin', 'Ethereum', 'XRP');

        for ($i = 0; $i < 10; $i++) {
            $varTempNameCrypto = $parsed_json->{'data'}[$i]->{'name'};
            if (in_array($varTempNameCrypto, $listOfOurNamesCrypto)) {
                $namesCrypto[] = $varTempNameCrypto;
                $shortnamesCrypto[] = $parsed_json->{'data'}[$i]->{'symbol'};
                $pricesCrypto = $parsed_json->{'data'}[$i]->{'quote'}->{'EUR'}->{'price'};
                $evols24hCryptoString = $parsed_json->{'data'}[$i]->{'quote'}->{'EUR'}->{'percent_change_24h'};
                $positionPoint = strpos($evols24hCryptoString, '.');
                $evols24hCrypto[] = floatval($evols24hCryptoString);
                if ((substr($evols24hCryptoString, 0, 1) == '-')) {
                    if ($positionPoint > 2) {
                        $evols24hString[] = 'decrease++';
                    } else {
                        if ((int)(substr($evols24hCryptoString, 1, 2)) > 4) {
                            $evols24hString[] = 'decrease++';
                        } else {
                            $evols24hString[] = 'decrease+';
                        }
                    }
                } else {
                    if ($positionPoint > 1) {
                        $evols24hString[] = 'increase++';
                    } else {
                        if ((int)(substr($evols24hCryptoString, 0, 1)) > 4) {
                            $evols24hString[] = 'increase++';
                        } else {
                            $evols24hString[] = 'increase+';
                        }
                    }
                }
            }
        }

        return $this->render('dashboardHomePage.html.twig', ['globalEarnings' => $globalEarnings, 'namesCrypto' => $namesCrypto, 'shortnamesCrypto' => $shortnamesCrypto, 'evol24hCrypto' => $evols24hCrypto, 'evol24hString' => $evols24hString]);
    }
}
