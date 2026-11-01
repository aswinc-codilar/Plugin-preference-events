<?php
declare(strict_types=1);

namespace Codilar\Testimonial\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Testimonial extends AbstractDb
{
    public const TABLE_NAME = 'custom_testimonial';

    /**
     * @inheritdoc
     */
    protected function _construct(): void
    {
        $this->_init(self::TABLE_NAME, 'testimonial_id');
    }
}
