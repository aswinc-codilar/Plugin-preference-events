<?php
declare(strict_types=1);

namespace Codilar\Testimonial\Model;

use Codilar\Testimonial\Model\ResourceModel\Testimonial as TestimonialResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Customer testimonial shown on the /testimonial storefront page.
 */
class Testimonial extends AbstractModel
{
    public const TESTIMONIAL_ID = 'testimonial_id';
    public const NAME = 'name';
    public const CITY = 'city';
    public const RATING = 'rating';
    public const MESSAGE = 'message';
    public const CREATED_AT = 'created_at';

    /**
     * @var string
     */
    protected $_eventPrefix = 'codilar_testimonial';

    /**
     * @inheritdoc
     */
    protected function _construct(): void
    {
        $this->_init(TestimonialResource::class);
    }
}
