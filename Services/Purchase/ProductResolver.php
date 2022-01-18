<?php
/*
* created on: 17/01/2022 - 17:44
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Services\Purchase;

use App\Entity\Purchase\Product;
use App\OtaNine\Services\ProductFactory;
use App\Repository\Purchase\ProductRepository;
use VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode\InvalidProductReferenceException;

/**
 * Class ProductResolver
 * @package App\VisageFour\Bundle\ToolsBundle\Services
 * 
 * Gets a 'purchase product' by reference: a default product or an Ota product
 */
class ProductResolver
{
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var ProductFactory
     */
    private $otaProductFactory;

    public function __construct(ProductRepository $productRepository, ProductFactory $otaProductFactory)
    {
        $this->productRepository = $productRepository;
        $this->otaProductFactory = $otaProductFactory;
    }

    /**
     * @param $ref
     * @return Product|null
     * @throws InvalidProductReferenceException
     *
     * attempt getting an Ota product first, if fails then attempt for a "default" product (from the DB)
     */
    public function getProductByReference($ref): ?Product
    {
        try {
            $otaProd = $this->otaProductFactory->getProductByReference($ref);
            return $otaProd;
        } catch (InvalidProductReferenceException $e) {
            // continue
        }

        $curProd = $this->productRepository->findOneBy([
            'reference' => $ref
        ]);

        if (empty($curProd)) {
            throw new InvalidProductReferenceException($ref);
        }

        return $curProd;
    }
}