<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AddNewTransactionControllerTest extends WebTestCase
{
    // Test permettant de vérifier que la page d'ajout d'une transaction est bien accessible et correctement renseignée
    public function testAddNewTransactionPageIsUp()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/ajouter-transaction');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('html title', 'Ajouter une nouvelle transaction');
        $this->assertSelectorTextContains('html h1', 'Ajouter une transaction');
        $this->assertSame(1, $crawler->filter('form')->count());
        $this->assertSelectorTextContains('html .alert', '');
        $this->assertSelectorTextContains('html button', 'Ajouter');
    }

    // Test permettant de vérifier que tout se passe bien lorsque le formulaire est soumis avec des données correctes
    public function testAddNewTransactionWithCorrectInformations()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/ajouter-transaction');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Ajouter')->form();
        $form['add_new_transaction_form[selectcrypto]'] = 'XRP';
        $form['add_new_transaction_form[quantity]'] = 1;
        $form['add_new_transaction_form[price]'] = 0.5;
        $crawler = $client->submit($form);

        $this->assertSelectorTextContains('html .alert', 'Votre nouvelle transaction a bien été ajoutée en base de données.');
    }

    // Test permettant de vérifier que l'utilisateur a bien un message d'alerte lorsque le champ 'Quantité' est mal renseigné
    public function testAddNewTransactionWithBadQuantityInformations()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/ajouter-transaction');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Ajouter')->form();
        $form['add_new_transaction_form[selectcrypto]'] = 'XRP';
        $form['add_new_transaction_form[quantity]'] = 'a';
        $form['add_new_transaction_form[price]'] = 0.5;
        $crawler = $client->submit($form);

        $this->assertSelectorTextContains('html .alert', 'Vous devez indiquer un nombre pour la quantité de la transaction.');
    }

    // Test permettant de vérifier que l'utilisateur a bien un message d'alerte lorsque le champ 'Prix d'achat' est mal renseigné
    public function testAddNewTransactionWithBadPriceInformations()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/ajouter-transaction');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Ajouter')->form();
        $form['add_new_transaction_form[selectcrypto]'] = 'XRP';
        $form['add_new_transaction_form[quantity]'] = 1;
        $form['add_new_transaction_form[price]'] = 'a';
        $crawler = $client->submit($form);

        $this->assertSelectorTextContains('html .alert', "Vous devez indiquer un nombre pour le prix d'achat.");
    }

    // Test vérifiant le lien renvoyant à la page d'accueil
    public function testAddNewTransactionPageRedirectsToHomePage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/ajouter-transaction');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $link = $crawler->selectLink('')->link();
        $crawler = $client->click($link);
        $this->assertSelectorTextContains('html h1', 'Crypto Tracker');
    }
}
