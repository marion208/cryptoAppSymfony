<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Controller\APIController;

class DashboardHomePageController extends AbstractController
{
    // Fonction permettant d'afficher la page d'accueil avec les informations du tableau de bord
    public function displayDashboardHomePage()
    {
        // Calcul des gains pour chacune des cryptos monnaies
        $entityManager = $this->getDoctrine()->getManager();
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
