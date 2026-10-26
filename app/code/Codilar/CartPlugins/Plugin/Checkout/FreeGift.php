<?php
declare(strict_types=1);

namespace Codilar\CartPlugins\Plugin\Checkout;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Model\Quote\Item;

/**
 * AFTER plugin on Cart::save().
 * Runs once the cart has been saved (after add, update and delete). If the cart holds a
 * phone priced above the threshold, a free phone cover is added at price 0; if not, any
 * gift already in the cart is removed. The cart is then saved once more to store the change.
 */
class FreeGift
{
    /** Custom option that marks the gift line and keeps it separate from a paid copy of the same case. */
    public const OPTION_CODE = 'codilar_free_gift';

    /** Stops the second save() below from running this plugin again. */
    private bool $isProcessing = false;

    private ?int $phonesCategoryId = null;

    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly CategoryCollectionFactory $categoryCollectionFactory,
        private readonly ManagerInterface $messageManager,
        private readonly string $phonesCategoryUrlKey,
        private readonly float $priceThreshold,
        private readonly string $giftSku
    ) {
    }

    public function afterSave(Cart $subject, Cart $result): Cart
    {
        if ($this->isProcessing) {
            return $result;
        }

        $quote = $subject->getQuote();
        $giftItem = null;
        $qualifies = false;
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getOptionByCode(self::OPTION_CODE)) {
                $giftItem = $item;
            } elseif ($this->isQualifyingPhone($item)) {
                $qualifies = true;
            }
        }

        // Nothing to change: gift present and earned, or absent and not earned (qty kept at 1).
        if ($qualifies === ($giftItem !== null) && (!$giftItem || (float)$giftItem->getQty() === 1.0)) {
            return $result;
        }

        $this->isProcessing = true;
        try {
            if ($qualifies && $giftItem) {
                $giftItem->setQty(1);
            } elseif ($qualifies) {
                if (!$this->addGift($quote)) {
                    return $result;
                }
                $this->messageManager->addSuccessMessage(__('A free phone cover has been added to your cart.'));
            } else {
                $quote->removeItem($giftItem->getId());
            }
            // The first save already collected totals and each address cached its item list,
            // so reset both or the gift line keeps its catalog price.
            $quote->setTotalsCollectedFlag(false);
            foreach ($quote->getAllAddresses() as $address) {
                $address->unsetData('cached_items_all');
            }
            $subject->save();
        } finally {
            $this->isProcessing = false;
        }

        return $result;
    }

    private function isQualifyingPhone(Item $item): bool
    {
        $product = $item->getProduct();

        return (float)$product->getFinalPrice() > $this->priceThreshold
            && in_array($this->getPhonesCategoryId(), array_map('intval', $product->getCategoryIds()), true);
    }

    private function addGift(\Magento\Quote\Model\Quote $quote): bool
    {
        try {
            // Clone so the custom option does not stick to the repository's cached product.
            $gift = clone $this->productRepository->get($this->giftSku, false, $quote->getStoreId());
        } catch (NoSuchEntityException) {
            return false;
        }
        if (!$gift->isSalable()) {
            return false;
        }

        $gift->addCustomOption(self::OPTION_CODE, '1');
        $item = $quote->addProduct($gift, 1);
        if (is_string($item)) {
            // addProduct() returns an error string when the product cannot be added.
            return false;
        }
        $item->setCustomPrice(0);
        $item->setOriginalCustomPrice(0);

        return true;
    }

    private function getPhonesCategoryId(): int
    {
        if ($this->phonesCategoryId === null) {
            $this->phonesCategoryId = (int)$this->categoryCollectionFactory->create()
                ->addAttributeToFilter('url_key', $this->phonesCategoryUrlKey)
                ->setPageSize(1)
                ->getFirstItem()
                ->getId();
        }

        return $this->phonesCategoryId;
    }
}
