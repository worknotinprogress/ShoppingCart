<?php

namespace App\Service;

use App\Entity\Customer;
use App\Entity\ShoppingCart;
use App\Repository\CustomerRepository;
use App\Repository\ShoppingCartRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ShoppingCartService
 * @package App\Service
 */
class ShoppingCartService
{
    /**
     * @var CustomerRepository
     */
    private $_customerRepository;

    /**
     * @var CustomerRepository
     */
    private $_shoppingCartRepository;

    /**
     * @var EntityManager|EntityManagerInterface
     */
    private $_em;

    /**
     * @var array
     */
    private $_currencies;

    /**
     * @var String
     */
    private $_defaultCurrency;

    /**
     * ShoppingCartService constructor.
     * @param CustomerRepository $customerRepository
     * @param ShoppingCartRepository $shoppingCartRepository
     * @param EntityManager|EntityManagerInterface $em
     * @param $currencies
     * @param $defaultCurrency
     */
    public function __construct(
        $currencies,
        $defaultCurrency,
        CustomerRepository $customerRepository,
        ShoppingCartRepository $shoppingCartRepository,
        EntityManagerInterface $em)
    {
        $this->_currencies = $currencies;
        $this->_defaultCurrency = $defaultCurrency;
        $this->_customerRepository = $customerRepository;
        $this->_shoppingCartRepository = $shoppingCartRepository;
        $this->_em = $em;
    }

    /**
     * @param array $products
     * @param Customer $customer
     */
    public function productToShoppingCart(array $products, Customer $customer)
    {
        foreach ($products as $product){
            $shoppingCart = $this->_shoppingCartRepository->findOneBy(['identifier' => $product['identifier'], 'customer' => $customer]);

            if (!$shoppingCart) {
                $shoppingCart = new ShoppingCart();
                $shoppingCart
                    ->setIdentifier($product['identifier'])
                    ->setCustomer($customer);
            }
            if (!empty($product['name'])) {
                $shoppingCart->setName($product['name']);
            }
            $quantity = $shoppingCart->getQuantity() + $product['quantity'];

            if (!empty($product['price']) && !empty($product['currency'])) {
                if ($product['quantity'] > 0){
                    $price = $this->_transformCurrencyToDefault($product['price'], $product['currency']);
                    $unitPrice = round($price / $product['quantity'], 6);
                    $shoppingCart->setPrice($unitPrice);
                    $shoppingCart->setCurrency($this->_defaultCurrency);
                }
            }

            $shoppingCart->setQuantity($quantity);

            if ($quantity <= 0){
                $this->_em->remove($shoppingCart);
            } else {
                $this->_em->persist($shoppingCart);
            }

            $this->_em->flush();
        }
    }

    /**
     * @param String $filePath
     * @return Customer
     */
    public function saveCustomerByFile(String $filePath)
    {
        $customer = $this->_customerRepository->findOneBy(['fileHash' => md5(basename($filePath))]);
        if (!$customer) {
            $customer = new Customer();
            $customer->setFileHash(md5($filePath));
            $this->_em->persist($customer);
            $this->_em->flush();
        }

        return $customer;
    }

    /**
     * @param String $filePath
     * @return ShoppingCart[]|array|\Doctrine\Common\Collections\Collection
     */
    public function getSpecificShoppingCart(String $filePath)
    {
        $customer = $this->_customerRepository->findOneBy(['fileHash' => md5(basename($filePath))]);
        if ($customer) {
            return $customer->getShoppingCart()->toArray();
        }
        return [];
    }

    /**
     * @return Customer[]
     */
    public function getAllShoppingCarts()
    {
        return $this->_customerRepository->findAll();
    }

    /**
     * @param Float $price
     * @param String $currency
     * @return Float|mixed
     */
    private function _transformCurrencyToDefault(Float $price, String $currency)
    {
        if (isset($this->_currencies[$currency])){
            $price /= $this->_currencies[$currency];
        }
        if (isset($this->_currencies[$this->_defaultCurrency])) {
            $price *= $this->_currencies[$this->_defaultCurrency];
        }

        return round($price, 6);
    }
}