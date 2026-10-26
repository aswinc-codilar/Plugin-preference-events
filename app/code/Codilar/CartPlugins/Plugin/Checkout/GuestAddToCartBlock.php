<?php
declare(strict_types=1);

namespace Codilar\CartPlugins\Plugin\Checkout;

use Codilar\CartPlugins\Setup\Patch\Data\AddLoginRequiredAttribute;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;

/**
 * BEFORE plugin on Cart::addProduct().
 * Runs before the product is added. If a guest tries to buy a product flagged
 * "Login Required to Buy", throwing stops addProduct() from running at all; the
 * add-to-cart controller catches the exception and shows the message.
 */
class GuestAddToCartBlock
{
    public function __construct(
        private readonly Session $customerSession,
        private readonly ProductRepositoryInterface $productRepository
    ) {
    }

    /**
     * @param int|Product $productInfo
     * @param mixed $requestInfo
     * @throws LocalizedException
     */
    public function beforeAddProduct(Cart $subject, $productInfo, $requestInfo = null): null
    {
        if ($this->customerSession->isLoggedIn()) {
            return null;
        }

        $product = $productInfo instanceof Product
            ? $productInfo
            : $this->productRepository->getById((int)$productInfo);

        if ($product->getData(AddLoginRequiredAttribute::ATTRIBUTE_CODE)) {
            throw new LocalizedException(__('Please log in to buy this product.'));
        }

        // null = leave the original arguments unchanged.
        return null;
    }
}
