<?php
declare(strict_types=1);

namespace Codilar\ProductReturns\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

/**
 * Hides the return attributes from the product page "More Information" tab;
 * they are shown in the return policy block below the Wishlist link instead.
 */
class HideReturnAttributesFromMoreInformation implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavSetupFactory $eavSetupFactory
    ) {
    }

    /**
     * @inheritdoc
     */
    public function apply(): self
    {
        $this->setVisibleOnFront(false);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function revert(): void
    {
        $this->setVisibleOnFront(true);
    }

    /**
     * @param bool $visible
     * @return void
     */
    private function setVisibleOnFront(bool $visible): void
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        foreach ([
            AddReturnProductAttributes::ATTRIBUTE_RETURN_ELIGIBLE,
            AddReturnProductAttributes::ATTRIBUTE_RETURN_WINDOW_DAYS,
        ] as $attributeCode) {
            $eavSetup->updateAttribute(Product::ENTITY, $attributeCode, 'is_visible_on_front', (int) $visible);
        }
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [AddReturnProductAttributes::class];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
