<?php

namespace App\Service;

use Symfony\Component\Config\Definition\Exception\Exception;


/**
 * Class ProductDataConverter
 * @package App\Service
 */
class ProductDataConverter
{
    /**
     * @var array
     */
    protected $products = [];

    /**
     * @var array
     */
    private $_currencies;

    /**
     * @var array
     */
    private $_errors = [];

    public function __construct($currencies)
    {
        $this->_currencies = $currencies;
    }

    /**
     * Converts product data
     *
     * @param array $productData
     * @return bool
     */
    public function convertProducts(array $productData)
    {
        if(empty($productData)){
            $this->_errors[] = 'No products found in file';
            return false;
        }

        foreach ($productData as $row => $item) {
            try {
                $this->_validateProduct($item);
             } catch (Exception $e){
                $this->_errors[] = sprintf('Error product in line %d could be added to cart: %s', $row + 1, $e->getMessage());
                continue;
            }

            $product['identifier'] = (string)$item[0];
            $product['name'] = (string)$item[1];
            $product['quantity'] = (int)$item[2];
            $product['price'] = (float)$item[3];
            $product['currency'] = (string)$item[4];

            $this->products[] = $product;
        }
    }

    /**
     * @return array
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Checks if errors exists
     *
     * @return bool
     */
    public function hasErrors()
    {
        if (!empty($this->_errors)){
            return true;
        }

        return false;
    }

    /**
     * Returns array of errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * @param array $product
     * @return bool
     */
    private function _validateProduct(array $product)
    {
        if (!isset($product[0]) || empty($product[0])){
            throw new Exception('Identifier is required');
        }
        if (!isset($product[2]) || !(is_integer($product[2]) || is_numeric(abs($product[2])))){
            throw new Exception('Quantity is required and has to be integer');
        }
        if (isset($product[3]) && !empty($product[3]) && !(is_float($product[3]) || is_numeric($product[3]))){
            throw new Exception('Price has to float format');
        }
        if (isset($product[4]) && !empty($product[4]) && !array_key_exists($product[4], $this->_currencies)){
            throw new Exception('Available currency types: '. json_encode(array_keys($this->_currencies)));
        }
        return true;
    }
}