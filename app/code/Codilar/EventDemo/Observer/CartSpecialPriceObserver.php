<?php
declare(strict_types=1);

namespace Codilar\EventDemo\Observer;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item;

/**
 * Listens to "checkout_cart_product_add_after", which Magento fires every time a product
 * is added to the cart. If it is the chosen product (Realme XT, normally $349.99), that
 * cart line gets a special price ($99). The catalog price itself is not changed.
 */
class CartSpecialPriceObserver implements ObserverInterface
{
    public function __construct(
        private readonly string $sku,
        private readonly float $specialPrice
    ) {
    }

    public function execute(Observer $observer): void
    {
        /** @var Product $product */
        $product = $observer->getEvent()->getData('product');
        if ($product->getSku() !== $this->sku) {
            return;
        }

        /** @var Item $item */
        $item = $observer->getEvent()->getData('quote_item');

        // Custom price applies to this cart line only. Cart::save(), which runs right after,
        // recalculates the totals and stores it.
        $item->setCustomPrice($this->specialPrice);
        $item->setOriginalCustomPrice($this->specialPrice);
    }
}
