<?php
declare(strict_types=1);

namespace Codilar\CartPlugins\Plugin\Checkout;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Exception\LocalizedException;

/**
 * AROUND plugin on Cart::addProduct().
 * Decides whether the original method runs at all: if the product is already in the
 * cart, $proceed() is never called. Throwing (rather than just returning) also stops the
 * controller from showing "You added ... to your shopping cart".
 * Quantity can still be changed on the cart page, which does not use addProduct().
 */
class DuplicateItemBlock
{
    /**
     * @param int|Product $productInfo
     * @param mixed $requestInfo
     * @throws LocalizedException
     */
    public function aroundAddProduct(Cart $subject, callable $proceed, $productInfo, $requestInfo = null): Cart
    {
        $productId = $productInfo instanceof Product ? (int)$productInfo->getId() : (int)$productInfo;

        foreach ($subject->getQuote()->getAllVisibleItems() as $item) {
            // The free gift line does not count, so the customer can still buy that case.
            if ($item->getOptionByCode(FreeGift::OPTION_CODE)) {
                continue;
            }
            if ((int)$item->getProductId() === $productId) {
                throw new LocalizedException(__('This item is already in your cart.'));
            }
        }

        return $proceed($productInfo, $requestInfo);
    }
}
