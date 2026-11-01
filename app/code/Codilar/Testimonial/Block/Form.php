<?php
declare(strict_types=1);

namespace Codilar\Testimonial\Block;

use Codilar\Testimonial\Controller\Index\Save;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * "Write a Testimonial" form; refills the fields after a failed submission.
 */
class Form extends Template
{
    /**
     * @var array|null
     */
    private ?array $formData = null;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param array $data
     */
    public function __construct(
        Context $context,
        private readonly DataPersistorInterface $dataPersistor,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * URL the form posts to.
     *
     * @return string
     */
    public function getFormAction(): string
    {
        return $this->getUrl('testimonial/index/save');
    }

    /**
     * Name of the hidden anti-spam field.
     *
     * @return string
     */
    public function getHoneypotFieldName(): string
    {
        return Save::HONEYPOT_FIELD;
    }

    /**
     * Value the visitor previously typed into a field, or an empty string.
     *
     * @param string $field
     * @return string
     */
    public function getFieldValue(string $field): string
    {
        if ($this->formData === null) {
            $this->formData = (array) $this->dataPersistor->get(Save::DATA_PERSISTOR_KEY);
            $this->dataPersistor->clear(Save::DATA_PERSISTOR_KEY);
        }

        return (string) ($this->formData[$field] ?? '');
    }
}
