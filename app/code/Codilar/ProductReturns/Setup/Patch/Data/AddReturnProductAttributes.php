<?php
declare(strict_types=1);

namespace Codilar\ProductReturns\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Backend\Boolean as BooleanBackend;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean as BooleanSource;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

/**
 * Adds "Return Eligible" and "Return Window (Days)" product attributes
 * to a "Returns" group in every product attribute set.
 */
class AddReturnProductAttributes implements DataPatchInterface, PatchRevertableInterface
{
    public const ATTRIBUTE_RETURN_ELIGIBLE = 'return_eligible';
    public const ATTRIBUTE_RETURN_WINDOW_DAYS = 'return_window_days';
    private const GROUP_NAME = 'Returns';

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
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        // Setting 'group' makes EavSetup create the group in, and assign the attribute to, every attribute set.
        $commonSettings = [
            'type' => 'int',
            'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
            'group' => self::GROUP_NAME,
            'visible' => true,
            'required' => false,
            'user_defined' => true,
            'visible_on_front' => true,
            'used_in_product_listing' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_in_advanced_search' => false,
            'is_used_in_grid' => true,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => true,
            'apply_to' => '',
        ];

        $eavSetup->addAttribute(
            Product::ENTITY,
            self::ATTRIBUTE_RETURN_ELIGIBLE,
            array_merge($commonSettings, [
                'label' => 'Return Eligible',
                'input' => 'boolean',
                'backend' => BooleanBackend::class,
                'source' => BooleanSource::class,
                'default' => '1',
                'sort_order' => 10,
            ])
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            self::ATTRIBUTE_RETURN_WINDOW_DAYS,
            array_merge($commonSettings, [
                'label' => 'Return Window (Days)',
                'input' => 'text',
                'frontend_class' => 'validate-digits',
                'note' => 'Leave empty to use the store default return policy.',
                'sort_order' => 20,
            ])
        );

        $this->moduleDataSetup->getConnection()->endSetup();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function revert(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->removeAttribute(Product::ENTITY, self::ATTRIBUTE_RETURN_ELIGIBLE);
        $eavSetup->removeAttribute(Product::ENTITY, self::ATTRIBUTE_RETURN_WINDOW_DAYS);

        $this->moduleDataSetup->getConnection()->endSetup();
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
