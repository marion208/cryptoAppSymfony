<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity)
 * @ORM\Table(name="transactions")
 */

class Transaction
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $idTransaction;

    /**
     * @ORM\Column(type="string")
     */
    private $nameCrypto;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateTransaction;

    /**
     * @ORM\Column(type="decimal", precision = 10, scale = 5)
     */
    private $quantity;

    /**
     * @ORM\Column(type="decimal", precision = 10, scale = 5)
     */
    private $unitAmount;

    public function __construct($nameCrypto, $dateTransaction, $quantity, $unitAmount)
    {
        $this->nameCrypto = $nameCrypto;
        $this->dateTransaction = $dateTransaction;
        $this->quantity = $quantity;
        $this->unitAmount = $unitAmount;
    }

    public function getIdTransaction()
    {
        return $this->idTransaction;
    }

    public function setIdTransaction($idTransaction)
    {
        $this->idTransaction = $idTransaction;
    }

    public function getNameCrypto()
    {
        return $this->nameCrypto;
    }

    public function setNameCrypto($nameCrypto)
    {
        $this->nameCrypto = $nameCrypto;
    }

    public function getDateTransaction()
    {
        return $this->dateTransaction;
    }

    public function setDateTransaction($dateTransaction)
    {
        $this->dateTransaction = $dateTransaction;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    public function getUnitAmount()
    {
        return $this->unitAmount;
    }

    public function setUnitAmount($unitAmount)
    {
        $this->unitAmount = $unitAmount;
    }
}
