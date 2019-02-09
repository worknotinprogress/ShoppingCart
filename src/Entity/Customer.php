<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CustomerRepository")
 */
class Customer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fileHash;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ShoppingCart", mappedBy="customer", orphanRemoval=true)
     */
    private $shoppingCart;

    /**
     * Customer constructor.
     */
    public function __construct()
    {
        $this->shoppingCart = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getFileHash(): ?string
    {
        return $this->fileHash;
    }

    /**
     * @param string $fileHash
     * @return Customer
     */
    public function setFileHash(string $fileHash): self
    {
        $this->fileHash = $fileHash;

        return $this;
    }

    /**
     * @return Collection|ShoppingCart[]
     */
    public function getShoppingCart(): Collection
    {
        return $this->shoppingCart;
    }

    public function addShoppingCart(ShoppingCart $shoppingCart): self
    {
        if (!$this->shoppingCart->contains($shoppingCart)) {
            $this->shoppingCart[] = $shoppingCart;
            $shoppingCart->setCustomer($this);
        }

        return $this;
    }

    /**
     * @param ShoppingCart $shoppingCart
     * @return Customer
     */
    public function removeShoppingCart(ShoppingCart $shoppingCart): self
    {
        if ($this->shoppingCart->contains($shoppingCart)) {
            $this->shoppingCart->removeElement($shoppingCart);
            // set the owning side to null (unless already changed)
            if ($shoppingCart->getCustomer() === $this) {
                $shoppingCart->setCustomer(null);
            }
        }

        return $this;
    }
}
