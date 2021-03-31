<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RemoveTransactionControllerTest extends WebTestCase
{
    // Test permettant de vérifier que la page de suppression d'un montant est bien accessible et correctement renseignée
    public function testRemoveTransactionPageIsUp()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/supprimer-transaction');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('html title', 'Supprimer un montant');
        $this->assertSelectorTextContains('html h1', 'Supprimer un montant');
        $this->assertSame(1, $crawler->filter('form')->count());
        $this->assertSelectorTextContains('html .alert', '');
        $this->assertSelectorTextContains('html button', 'Valider');
    }

    // Test permettant de vérifier que tout se passe bien lorsque le formulaire est soumis avec des données correctes
    public function testRemoveTransactionWithCorrectInformations()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/supprimer-transaction');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Valider')->form();
        $form['remove_transaction_form[selectcrypto]'] = 'XRP';
        $form['remove_transaction_form[quantity]'] = 1;
        $crawler = $client->submit($form);

        $this->assertSelectorTextContains('html .alert', 'Votre suppression a bien été prise en compte.');
    }

    // Test permettant de vérifier que l'utilisateur a bien un message d'alerte lorsque le champ 'Quantité' est mal renseigné
    public function testRemoveTransactionWithBadQuantityInformations()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/supprimer-transaction');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Valider')->form();
        $form['remove_transaction_form[selectcrypto]'] = 'XRP';
        $form['remove_transaction_form[quantity]'] = 'a';
        $crawler = $client->submit($form);

        $this->assertSelectorTextContains('html .alert', 'Vous devez indiquer un nombre dans le champ quantité.');
    }

    // Test vérifiant le lien renvoyant à la page d'accueil
    public function testRemoveTransactionPageRedirectsToHomePage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/supprimer-transaction');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $link = $crawler->selectLink('')->link();
        $crawler = $client->click($link);
        $this->assertSelectorTextContains('html h1', 'Crypto Tracker');
    }
}
