<?php
declare(strict_types=1);

namespace Codilar\MenuBadge\Model\Category\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Options for the "Menu Badge" category attribute.
 */
class MenuBadge extends AbstractSource
{
    public const NONE = '';
    public const NEW = 'new';
    public const HOT = 'hot';
    public const SALE = 'sale';

    public function getAllOptions(): array
    {
        if ($this->_options === null) {
            $this->_options = [
                ['value' => self::NONE, 'label' => __('None')],
                ['value' => self::NEW, 'label' => __('New')],
                ['value' => self::HOT, 'label' => __('Hot')],
                ['value' => self::SALE, 'label' => __('Sale')],
            ];
        }

        return $this->_options;
    }
}
