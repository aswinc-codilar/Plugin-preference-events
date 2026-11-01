<?php
declare(strict_types=1);

namespace Codilar\UaeWebsite\Setup\Patch\Data;

use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\ResourceModel\Group as GroupResource;
use Magento\Store\Model\ResourceModel\Store as StoreResource;
use Magento\Store\Model\ResourceModel\Website as WebsiteResource;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\WebsiteFactory;

/**
 * Turns the main website into the India (INR) website and adds a UAE (AED) website
 * with its own store and store view, sharing the main catalog.
 */
class CreateUaeWebsite implements DataPatchInterface
{
    public const WEBSITE_CODE = 'uae';
    public const STORE_CODE = 'uae';
    private const MAIN_WEBSITE_CODE = 'base';
    private const INR_TO_AED_RATE = 0.0420;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param WebsiteFactory $websiteFactory
     * @param WebsiteResource $websiteResource
     * @param GroupFactory $groupFactory
     * @param GroupResource $groupResource
     * @param StoreFactory $storeFactory
     * @param StoreResource $storeResource
     * @param WriterInterface $configWriter
     * @param CurrencyFactory $currencyFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ProductAction $productAction
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly WebsiteFactory $websiteFactory,
        private readonly WebsiteResource $websiteResource,
        private readonly GroupFactory $groupFactory,
        private readonly GroupResource $groupResource,
        private readonly StoreFactory $storeFactory,
        private readonly StoreResource $storeResource,
        private readonly WriterInterface $configWriter,
        private readonly CurrencyFactory $currencyFactory,
        private readonly ProductCollectionFactory $productCollectionFactory,
        private readonly ProductAction $productAction
    ) {
    }

    /**
     * @inheritdoc
     */
    public function apply(): self
    {
        $this->moduleDataSetup->startSetup();

        $mainWebsite = $this->websiteFactory->create();
        $this->websiteResource->load($mainWebsite, self::MAIN_WEBSITE_CODE, 'code');
        $mainWebsite->setName('India Website');
        $this->websiteResource->save($mainWebsite);

        $website = $this->createUaeWebsite((int) $mainWebsite->getDefaultGroup()->getRootCategoryId());
        $this->saveConfig((int) $website->getId());
        $this->currencyFactory->create()->saveRates(['INR' => ['AED' => self::INR_TO_AED_RATE]]);
        $this->assignAllProducts((int) $website->getId());

        $this->moduleDataSetup->endSetup();

        return $this;
    }

    /**
     * @param int $rootCategoryId
     * @return \Magento\Store\Model\Website
     */
    private function createUaeWebsite(int $rootCategoryId): \Magento\Store\Model\Website
    {
        $website = $this->websiteFactory->create();
        $website->setCode(self::WEBSITE_CODE)->setName('UAE Website')->setSortOrder(10);
        $this->websiteResource->save($website);

        $group = $this->groupFactory->create();
        $group->setWebsiteId($website->getId())
            ->setCode('uae_store')
            ->setName('UAE Store')
            ->setRootCategoryId($rootCategoryId);
        $this->groupResource->save($group);

        $store = $this->storeFactory->create();
        $store->setCode(self::STORE_CODE)
            ->setName('UAE English')
            ->setWebsiteId($website->getId())
            ->setGroupId($group->getId())
            ->setSortOrder(0)
            ->setIsActive(1);
        $this->storeResource->save($store);

        $group->setDefaultStoreId($store->getId());
        $this->groupResource->save($group);
        $website->setDefaultGroupId($group->getId());
        $this->websiteResource->save($website);

        return $website;
    }

    /**
     * @param int $uaeWebsiteId
     * @return void
     */
    private function saveConfig(int $uaeWebsiteId): void
    {
        $default = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        // Website price scope lets each website keep its own base currency
        $this->configWriter->save('catalog/price/scope', '1', $default);
        $this->configWriter->save('web/url/use_store', '1', $default);

        // India (default scope)
        $this->configWriter->save('currency/options/base', 'INR', $default);
        $this->configWriter->save('currency/options/default', 'INR', $default);
        $this->configWriter->save('currency/options/allow', 'INR', $default);
        $this->configWriter->save('general/country/default', 'IN', $default);
        $this->configWriter->save('general/locale/timezone', 'Asia/Kolkata', $default);

        // UAE website
        $websites = ScopeInterface::SCOPE_WEBSITES;
        $this->configWriter->save('currency/options/base', 'AED', $websites, $uaeWebsiteId);
        $this->configWriter->save('currency/options/default', 'AED', $websites, $uaeWebsiteId);
        $this->configWriter->save('currency/options/allow', 'AED', $websites, $uaeWebsiteId);
        $this->configWriter->save('general/country/default', 'AE', $websites, $uaeWebsiteId);
        $this->configWriter->save('general/locale/timezone', 'Asia/Dubai', $websites, $uaeWebsiteId);
    }

    /**
     * @param int $websiteId
     * @return void
     */
    private function assignAllProducts(int $websiteId): void
    {
        $productIds = $this->productCollectionFactory->create()->getAllIds();
        if ($productIds) {
            $this->productAction->updateWebsites($productIds, [$websiteId], 'add');
        }
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
