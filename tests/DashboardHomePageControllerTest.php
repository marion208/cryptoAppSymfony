<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DashboardHomePageControllerTest extends WebTestCase
{
    // Test permettant de vérifier que la page d'accueil est bien accessible et correctement renseignée
    public function testHomePageIsUp()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('html title', 'Accueil');
        $this->assertSelectorTextContains('html h1', 'Crypto Tracker');
        $this->assertSelectorTextContains('html .showEarnings', '€');
        $this->assertSame(1, $crawler->filter('html .imgPlusSign')->count());
        $this->assertSame(1, $crawler->filter('html .imgPen')->count());
        $this->assertSame(3, $crawler->filter('html .rowDashboard')->count());
        $this->assertSame(9, $crawler->filter('html .columnDashboard')->count());
        $this->assertSame(3, $crawler->filter('html .shortnameCrypto')->count());
        $this->assertSame(3, $crawler->filter('html .nameCrypto')->count());
    }

    // Test vérifiant le lien renvoyant à la page de suppression d'un montant
    public function testHomePageRedirectsToFormRemoveNewTransaction()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $link = $crawler->filter('a[class="link_remove_transaction"]')->attr('href');
        $crawler = $client->request('GET', $link);
        $this->assertSelectorTextContains('html h1', 'Supprimer un montant');
    }

    // Test vérifiant le lien renvoyant à la page d'ajout d'une transaction
    public function testHomePageRedirectsToFormAddNewTransaction()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $link = $crawler->filter('a[class="link_add_transaction"]')->attr('href');
        $crawler = $client->request('GET', $link);
        $this->assertSelectorTextContains('html h1', 'Ajouter une transaction');
    }

    // Test vérifiant le lien renvoyant à la page du graphique d'évolution des gains
    public function testHomePageRedirectsToGraphEvolutionEarnings()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $link = $crawler->filter('a[class="link_graph"]')->attr('href');
        $crawler = $client->request('GET', $link);
        $this->assertSelectorTextContains('html h1', 'Vos gains');
    }
}
