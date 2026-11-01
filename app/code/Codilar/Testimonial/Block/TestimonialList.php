<?php
declare(strict_types=1);

namespace Codilar\Testimonial\Block;

use Codilar\Testimonial\Model\ResourceModel\Testimonial\Collection;
use Codilar\Testimonial\Model\ResourceModel\Testimonial\CollectionFactory;
use Codilar\Testimonial\Model\Testimonial;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Theme\Block\Html\Pager;

/**
 * Paginated list of testimonials, newest first.
 */
class TestimonialList extends Template
{
    public const PAGE_SIZE = 10;

    /**
     * @var Collection|null
     */
    private ?Collection $collection = null;

    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        private readonly CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Testimonials for the current page.
     *
     * @return Collection
     */
    public function getTestimonials(): Collection
    {
        if ($this->collection === null) {
            $this->collection = $this->collectionFactory->create()
                ->addOrder(Testimonial::CREATED_AT, Collection::SORT_ORDER_DESC)
                ->addOrder(Testimonial::TESTIMONIAL_ID, Collection::SORT_ORDER_DESC);
        }

        return $this->collection;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        /** @var Pager $pager */
        $pager = $this->getLayout()->createBlock(Pager::class, 'codilar.testimonial.pager');
        $pager->setLimit(self::PAGE_SIZE)
            ->setAvailableLimit([self::PAGE_SIZE => self::PAGE_SIZE])
            ->setShowPerPage(false)
            ->setShowAmounts(false)
            ->setCollection($this->getTestimonials());
        $this->setChild('pager', $pager);

        return $this;
    }

    /**
     * Pager HTML (empty when everything fits on one page).
     *
     * @return string
     */
    public function getPagerHtml(): string
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Created date formatted for the current locale.
     *
     * @param Testimonial $testimonial
     * @return string
     */
    public function getFormattedDate(Testimonial $testimonial): string
    {
        return $this->formatDate($testimonial->getData(Testimonial::CREATED_AT), \IntlDateFormatter::MEDIUM);
    }
}
