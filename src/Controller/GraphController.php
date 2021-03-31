<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\EvolutionCryptos;
use App\Entity\Transaction;
use DateTime;

class GraphController extends AbstractController
{
    // Fonction permettant d'afficher la page du grpahique d'évolution des gains, et d'ajouter en base de données les informations liées à la mise à jour du montant des crypto monnaies si cela n'a pas encore été fait dans la journée
    public function displayGraphEarningsPage(ChartBuilderInterface $chartBuilder): Response
    {
        // Récupération des données des gains dans la table d'évolution des cryptos
        $entityManager = $this->getDoctrine()->getManager();
        $query = $entityManager->getRepository(EvolutionCryptos::class);
        $historicOfEvolutionCryptos = $query->findAll();

        // Création d'une liste permettant de gérer les dates des différentes évolutions présentes en base de données
        $listOfDates = array();
        foreach ($historicOfEvolutionCryptos as $evolutionCryptos) {
            $timestampOfTransaction = $evolutionCryptos->getDateEvolutionOfCrypto();
            $dateOfTransaction = $timestampOfTransaction->format('d-m-y');
            array_push($listOfDates, $dateOfTransaction);
        }

        $currentTime = new DateTime('now');
        $dateOfCurrentTime = $currentTime->format('d-m-y');

        // On vérifie si on a déjà mis à jour aujourd'hui les cours de nos trois crypto monnaies. Si ce n'est pas le cas, on les met à jour
        if (!in_array($dateOfCurrentTime, $listOfDates)) {
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
        $query = $entityManager->getRepository(EvolutionCryptos::class);
        $historicOfEvolutionCryptos = $query->findAll();

        // On créé une liste de dates pour la légende de l'axe des abscisses
        $listOfDateEvolutions = array();
        foreach ($historicOfEvolutionCryptos as $evolutionOfCrypto) {
            $dateOfEvolution = $evolutionOfCrypto->getDateEvolutionOfCrypto();
            if (!in_array($dateOfEvolution, $listOfDateEvolutions)) {
                array_push($listOfDateEvolutions, $dateOfEvolution);
            }
        };

        // Création d'une nouvelle liste pour adapter le format de la date
        $labelDateEvolutions = array();
        foreach ($listOfDateEvolutions as $dateEvolutions) {
            $dateEvolutionsFormatted = $dateEvolutions->format('d-m-y');
            array_push($labelDateEvolutions, $dateEvolutionsFormatted);
        }

        // Création d'une liste pour gérer les données de gains à afficher sur le graphique
        $gainsPerEvolutions = array();
        for ($i = 0; $i < count($listOfDateEvolutions); $i++) {
            $gainsPerEvolutions[$i] = 0;
            foreach ($historicOfEvolutionCryptos as $evolutionOfCrypto) {
                $dateOfEvolution = $evolutionOfCrypto->getDateEvolutionOfCrypto();
                if ($dateOfEvolution == $listOfDateEvolutions[$i]) {
                    $gainsForTheCrypto = $evolutionOfCrypto->getEarnings();
                    $gainsPerEvolutions[$i] += $gainsForTheCrypto;
                }
            };
        }

        // Création du graphique
        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'type' => 'line',
            'labels' => $labelDateEvolutions,
            'datasets' => [
                [
                    'label' => 'Evolution des gains en €',
                    'borderColor' => 'rgb(31, 195, 108)',
                    'data' => $gainsPerEvolutions,
                ],
            ],
        ]);

        // Options pour la mise en forme du graphique
        $chart->setOptions([
            'layout' => [
                'padding' => [
                    'top' => 100,
                    'bottom' => 50,
                ],
            ],
            'legend' => [
                'display' => false,
            ],
            'aspectRatio' => 1.5

        ]);

        return $this->render('graph.html.twig', [
            'chart' => $chart,
        ]);
    }
}
