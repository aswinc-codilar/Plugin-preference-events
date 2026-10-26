<?php
declare(strict_types=1);

namespace Codilar\PreferenceDemo\Block\Html\Header;

use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\View\Element\Template\Context;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Theme\Block\Html\Header\Logo as CoreLogo;

/**
 * Replaces the core header logo block (see etc/frontend/di.xml).
 * Guests get the normal logo from the parent class; logged-in customers get the
 * "Members" logo shipped in view/frontend/web/images.
 */
class Logo extends CoreLogo
{
    private const MEMBERS_LOGO = 'Codilar_PreferenceDemo::images/logo-members.svg';

    public function __construct(
        Context $context,
        Database $fileStorageHelper,
        private readonly HttpContext $httpContext,
        array $data = []
    ) {
        parent::__construct($context, $fileStorageHelper, $data);
    }

    public function getLogoSrc()
    {
        if (!$this->isLoggedIn()) {
            return parent::getLogoSrc();
        }

        return $this->getViewFileUrl(self::MEMBERS_LOGO);
    }

    public function getLogoAlt()
    {
        if (!$this->isLoggedIn()) {
            return parent::getLogoAlt();
        }

        return trim(parent::getLogoAlt() . ' ' . __('Members'));
    }

    /**
     * Uses the HTTP context, not the customer session: on full-page-cached pages Magento
     * clears the session before blocks render, but the HTTP context is kept, and it is
     * part of the page cache key, so guests and members get separate cached pages.
     */
    private function isLoggedIn(): bool
    {
        return (bool)$this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
    }
}
