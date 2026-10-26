<?php
declare(strict_types=1);

namespace Codilar\MinicartCount\CustomerData;

use Magento\Checkout\CustomerData\Cart as CoreCart;

/**
 * Replaces the core minicart data source (see etc/frontend/di.xml).
 * Core getSummaryCount() is protected, so it is overridden directly - no core code copied.
 */
class Cart extends CoreCart
{
    /**
     * Number of different products in the cart, e.g. 2 for "3 x shirt + 2 x bag".
     * Core returns the total quantity (5 in that example).
     *
     * @return int
     */
    protected function getSummaryCount()
    {
        return count($this->getQuote()->getAllVisibleItems());
    }

    /**
     * Adds the total quantity, which the minicart no longer gets from summary_count,
     * so the minicart can show both numbers.
     *
     * @return array
     */
    public function getSectionData()
    {
        $data = parent::getSectionData();
        $data['codilar_total_qty'] = (float)$this->getQuote()->getItemsQty();

        return $data;
    }
}
