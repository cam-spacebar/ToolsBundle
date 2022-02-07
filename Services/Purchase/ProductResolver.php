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
     * PurchasePorduct creation behaviour: https://docs.google.com/presentation/d/1citBtVYpSjApO_aNBUNsebRe2WtIktSRkMRMpbxsS9w/edit#slide=id.g112c8113523_0_0
     * (see note: #2)
     */
    public function getProductByReference($ref): ?Product
    {
        try {
            // look for an OtaProduct first:
            $purchaseProductTemp = $this->otaToPurchaseProductConverter->getPurchaseProductByReference($ref);

            if (!empty($purchaseProductTemp)) {
                return $this->productRepository->getPurchaseProductCanonical($purchaseProductTemp);
            }
        } catch (InvalidProductReferenceException $e) {
            // there's no OTA product, so just continue...
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