<?php
/*
* created on: 17/01/2022 - 17:44
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Services\Purchase;

use App\Entity\Purchase\Product;
use App\OtaNine\Services\OtaToPurchaseProductConverter;
use App\OtaNine\Services\ProductFactory;
use App\Repository\Purchase\ProductRepository;
use VisageFour\Bundle\ToolsBundle\Exceptions\ApiErrorCode\InvalidProductReferenceException;

/**
 * Class ProductResolver
 * @package App\VisageFour\Bundle\ToolsBundle\Services
 * 
 * Gets a 'purchase product' by reference: a default 'purchase product' or an Ota product
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

    /**
     * @var OtaToPurchaseProductConverter
     */
    private $otaToPurchaseProductConverter;

    public function __construct(ProductRepository $productRepository, ProductFactory $otaProductFactory, OtaToPurchaseProductConverter $otaToPurchaseProductConverter)
    {
        $this->productRepository = $productRepository;
        $this->otaProductFactory = $otaProductFactory;
        $this->otaToPurchaseProductConverter = $otaToPurchaseProductConverter;
    }

    /**
     * @param $ref
     * @return Product|null
     * @throws InvalidProductReferenceException
     *
     * Attempt getting an Ota product first, if fails then attempt for a "default" product (from the DB)
     */
    public function getProductByReference($ref): ?Product
    {
        try {
            // if TicketTypeNotFoundException() thrown, let it go through.
            return $this->otaToPurchaseProductConverter->getPurchaseProductByReference($ref);
        } catch (InvalidProductReferenceException $e) {
            // there's no OTA product, so just continue to next
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