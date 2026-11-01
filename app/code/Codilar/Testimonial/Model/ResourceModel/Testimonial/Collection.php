<?php
declare(strict_types=1);

namespace Codilar\Testimonial\Model\ResourceModel\Testimonial;

use Codilar\Testimonial\Model\ResourceModel\Testimonial as TestimonialResource;
use Codilar\Testimonial\Model\Testimonial;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'testimonial_id';

    /**
     * @inheritdoc
     */
    protected function _construct(): void
    {
        $this->_init(Testimonial::class, TestimonialResource::class);
    }
}
