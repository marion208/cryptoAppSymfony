<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="evolutions_of_cryptos")
 */

class EvolutionCryptos
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $nameOfCrypto;

    /**
     * @ORM\Column(type="decimal", precision = 15, scale = 5)
     */
    private $priceEvolutionOfCrypto;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateEvolutionPrice;

    /**
     * @ORM\Column(type="decimal", precision = 10, scale = 5)
     */
    private $currentQuantityOwned;

    /**
     * @ORM\Column(type="decimal", precision = 15, scale = 5)
     */
    private $averagePriceOwned;

    /**
     * @ORM\Column(type="decimal", precision = 10, scale = 5)
     */
    private $earnings;

    public function __construct($nameOfCrypto, $priceEvolutionOfCrypto, $dateEvolutionPrice, $currentQuantityOwned, $averagePriceOwned, $earnings)
    {
        $this->nameOfCrypto = $nameOfCrypto;
        $this->priceEvolutionOfCrypto = $priceEvolutionOfCrypto;
        $this->dateEvolutionPrice = $dateEvolutionPrice;
        $this->currentQuantityOwned = $currentQuantityOwned;
        $this->averagePriceOwned = $averagePriceOwned;
        $this->earnings = $earnings;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getNameOfCrypto()
    {
        return $this->nameOfCrypto;
    }

    public function setNameOfCrypto($nameOfCrypto)
    {
        $this->nameOfCrypto = $nameOfCrypto;
    }

    public function getPriceEvolutionOfCrypto()
    {
        return $this->priceEvolutionOfCrypto;
    }

    public function setPriceEvolutionOfCrypto($priceEvolutionOfCrypto)
    {
        $this->priceEvolutionOfCrypto = $priceEvolutionOfCrypto;
    }

    public function getDateEvolutionOfCrypto()
    {
        return $this->dateEvolutionPrice;
    }

    public function setDateEvolutionOfCrypto($dateEvolutionPrice)
    {
        $this->dateEvolutionPrice = $dateEvolutionPrice;
    }

    public function getCurrentQuantityOwned()
    {
        return $this->currentQuantityOwned;
    }

    public function setCurrentQuantityOwned($currentQuantityOwned)
    {
        $this->currentQuantityOwned = $currentQuantityOwned;
    }

    public function getAveragePriceOwned()
    {
        return $this->averagePriceOwned;
    }

    public function setAveragePriceOwned($averagePriceOwned)
    {
        $this->averagePriceOwned = $averagePriceOwned;
    }

    public function getEarnings()
    {
        return $this->earnings;
    }

    public function setEarnings($earnings)
    {
        $this->earnings = $earnings;
    }
}
