<?php
declare(strict_types=1);

namespace Codilar\PreferenceDemo\Block\Html;

use Magento\Store\Model\ScopeInterface;
use Magento\Theme\Block\Html\Footer as CoreFooter;

/**
 * Replaces the core footer block (see etc/frontend/di.xml).
 * Core getCopyright() calls the private replaceCurrentYear(), which a child class cannot
 * override: PHP always calls the parent's own private method. So getCopyright() is
 * reimplemented here to call our replaceYearRange() instead.
 * Copied from Magento_Theme 2.4.9 Footer::getCopyright() - re-check on upgrade.
 */
class Footer extends CoreFooter
{
    private const START_YEAR = '2015';

    public function getCopyright()
    {
        if (!$this->_copyright) {
            $this->_copyright = $this->_scopeConfig->getValue(
                'design/footer/copyright',
                ScopeInterface::SCOPE_STORE
            );
        }
        return $this->replaceYearRange((string)__($this->_copyright));
    }

    /**
     * Replace {YYYY} with a year range, e.g. "2015–2026", instead of only the current year
     */
    private function replaceYearRange(string $text): string
    {
        return str_replace('{YYYY}', self::START_YEAR . '–' . (new \DateTime())->format('Y'), $text);
    }
}
