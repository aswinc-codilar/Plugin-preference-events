<?php
declare(strict_types=1);

namespace Codilar\Testimonial\Controller\Index;

use Codilar\Testimonial\Model\ResourceModel\Testimonial as TestimonialResource;
use Codilar\Testimonial\Model\Testimonial;
use Codilar\Testimonial\Model\TestimonialFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Validates and stores a testimonial submitted from the storefront form.
 */
class Save implements HttpPostActionInterface
{
    public const DATA_PERSISTOR_KEY = 'codilar_testimonial_form';

    /**
     * Hidden field that real visitors never fill in.
     */
    public const HONEYPOT_FIELD = 'website';

    private const MESSAGE_MIN_LENGTH = 20;
    private const MESSAGE_MAX_LENGTH = 1000;
    private const NAME_MAX_LENGTH = 255;
    private const CITY_MAX_LENGTH = 100;

    /**
     * @param RequestInterface $request
     * @param RedirectFactory $redirectFactory
     * @param FormKeyValidator $formKeyValidator
     * @param ManagerInterface $messageManager
     * @param DataPersistorInterface $dataPersistor
     * @param TestimonialFactory $testimonialFactory
     * @param TestimonialResource $testimonialResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly RequestInterface $request,
        private readonly RedirectFactory $redirectFactory,
        private readonly FormKeyValidator $formKeyValidator,
        private readonly ManagerInterface $messageManager,
        private readonly DataPersistorInterface $dataPersistor,
        private readonly TestimonialFactory $testimonialFactory,
        private readonly TestimonialResource $testimonialResource,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @inheritdoc
     */
    public function execute(): Redirect
    {
        $redirect = $this->redirectFactory->create()->setPath('testimonial');

        if (!$this->formKeyValidator->validate($this->request)) {
            $this->messageManager->addErrorMessage(__('Invalid form key. Please refresh the page and try again.'));
            return $redirect;
        }

        // Bots fill every field; pretend success so they get no signal.
        if (trim((string) $this->request->getParam(self::HONEYPOT_FIELD)) !== '') {
            $this->messageManager->addSuccessMessage(__('Thank you for your testimonial!'));
            return $redirect;
        }

        $data = [
            Testimonial::NAME => $this->clean($this->request->getParam(Testimonial::NAME)),
            Testimonial::CITY => $this->clean($this->request->getParam(Testimonial::CITY)),
            Testimonial::RATING => $this->clean($this->request->getParam(Testimonial::RATING)),
            Testimonial::MESSAGE => $this->clean($this->request->getParam(Testimonial::MESSAGE)),
        ];

        $errors = $this->validate($data);
        if ($errors) {
            foreach ($errors as $error) {
                $this->messageManager->addErrorMessage($error);
            }
            $this->dataPersistor->set(self::DATA_PERSISTOR_KEY, $data);
            return $redirect;
        }

        try {
            $testimonial = $this->testimonialFactory->create();
            $testimonial->setData([
                Testimonial::NAME => $data[Testimonial::NAME],
                Testimonial::CITY => $data[Testimonial::CITY] !== '' ? $data[Testimonial::CITY] : null,
                Testimonial::RATING => (int) $data[Testimonial::RATING],
                Testimonial::MESSAGE => $data[Testimonial::MESSAGE],
            ]);
            $this->testimonialResource->save($testimonial);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            $this->messageManager->addErrorMessage(
                __('We could not save your testimonial right now. Please try again later.')
            );
            $this->dataPersistor->set(self::DATA_PERSISTOR_KEY, $data);
            return $redirect;
        }

        $this->dataPersistor->clear(self::DATA_PERSISTOR_KEY);
        $this->messageManager->addSuccessMessage(__('Thank you for your testimonial!'));

        return $redirect;
    }

    /**
     * Trim and strip HTML tags from a request value.
     *
     * @param mixed $value
     * @return string
     */
    private function clean(mixed $value): string
    {
        return is_scalar($value) ? trim(strip_tags((string) $value)) : '';
    }

    /**
     * Return a list of validation error messages (empty when the data is valid).
     *
     * @param array $data
     * @return \Magento\Framework\Phrase[]
     */
    private function validate(array $data): array
    {
        $errors = [];

        if ($data[Testimonial::NAME] === '') {
            $errors[] = __('Please enter your name.');
        } elseif (mb_strlen($data[Testimonial::NAME]) > self::NAME_MAX_LENGTH) {
            $errors[] = __('Name must be %1 characters or fewer.', self::NAME_MAX_LENGTH);
        }

        if (mb_strlen($data[Testimonial::CITY]) > self::CITY_MAX_LENGTH) {
            $errors[] = __('City must be %1 characters or fewer.', self::CITY_MAX_LENGTH);
        }

        $rating = filter_var(
            $data[Testimonial::RATING],
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 5]]
        );
        if ($rating === false) {
            $errors[] = __('Please choose a rating between 1 and 5 stars.');
        }

        $messageLength = mb_strlen($data[Testimonial::MESSAGE]);
        if ($messageLength === 0) {
            $errors[] = __('Please enter your message.');
        } elseif ($messageLength < self::MESSAGE_MIN_LENGTH || $messageLength > self::MESSAGE_MAX_LENGTH) {
            $errors[] = __(
                'Your message must be between %1 and %2 characters.',
                self::MESSAGE_MIN_LENGTH,
                self::MESSAGE_MAX_LENGTH
            );
        }

        return $errors;
    }
}
