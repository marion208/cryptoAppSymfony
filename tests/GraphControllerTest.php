<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GraphControllerTest extends WebTestCase
{
    // Test permettant de vérifier que la page du graphique d'évolution des gains est bien accessible et correctement renseignée
    public function testGraphPageIsUp()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/graphique-gains');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('html title', 'Evolution des gains');
        $this->assertSelectorTextContains('html h1', 'Vos gains');
        $this->assertSame(1, $crawler->filter('canvas')->count());

        $link = $crawler->selectLink('')->link();
        $crawler = $client->click($link);
        $this->assertSelectorTextContains('html h1', 'Crypto Tracker');
    }

    // Test vérifiant le lien renvoyant à la page d'accueil
    public function testGraphPageRedirectsToHomePage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/graphique-gains');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $link = $crawler->selectLink('')->link();
        $crawler = $client->click($link);
        $this->assertSelectorTextContains('html h1', 'Crypto Tracker');
    }
}
