<?php
declare(strict_types=1);

namespace Codilar\HelloWorld\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;

class Index implements HttpGetActionInterface
{
    public function __construct(
        private readonly RawFactory $rawFactory
    ) {
    }

    public function execute(): Raw
    {
        return $this->rawFactory->create()->setContents('Hello World');
    }
}
