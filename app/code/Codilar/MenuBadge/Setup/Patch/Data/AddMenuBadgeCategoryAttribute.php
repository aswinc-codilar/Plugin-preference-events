<?php
declare(strict_types=1);

namespace Codilar\MenuBadge\Setup\Patch\Data;

use Codilar\MenuBadge\Model\Category\Attribute\Source\MenuBadge;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

/**
 * Adds the store-view scoped "Menu Badge" category attribute, rendered next to
 * the category name in the storefront top menu (see Plugin\Block\Topmenu).
 */
class AddMenuBadgeCategoryAttribute implements DataPatchInterface, PatchRevertableInterface
{
    public const ATTRIBUTE_CODE = 'menu_badge';

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavSetupFactory $eavSetupFactory
    ) {
    }

    public function apply(): self
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(Category::ENTITY, self::ATTRIBUTE_CODE, [
            'type' => 'varchar',
            'label' => 'Menu Badge',
            'input' => 'select',
            'source' => MenuBadge::class,
            'default' => MenuBadge::NONE,
            'global' => Attribute::SCOPE_STORE,
            'required' => false,
            'user_defined' => true,
            'visible' => true,
            'group' => 'Display Settings',
            'sort_order' => 65,
        ]);

        return $this;
    }

    public function revert(): void
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->removeAttribute(Category::ENTITY, self::ATTRIBUTE_CODE);
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
