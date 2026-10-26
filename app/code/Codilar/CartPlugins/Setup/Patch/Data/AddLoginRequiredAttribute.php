<?php
declare(strict_types=1);

namespace Codilar\CartPlugins\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Adds the "Login Required to Buy" Yes/No product attribute.
 * Products with it set to Yes cannot be added to the cart by guests
 * (see Plugin\Checkout\GuestAddToCartBlock).
 */
class AddLoginRequiredAttribute implements DataPatchInterface
{
    public const ATTRIBUTE_CODE = 'login_required_to_buy';

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavSetupFactory $eavSetupFactory
    ) {
    }

    public function apply(): self
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(Product::ENTITY, self::ATTRIBUTE_CODE, [
            'type' => 'int',
            'label' => 'Login Required to Buy',
            'input' => 'boolean',
            'source' => Boolean::class,
            'default' => 0,
            'global' => Attribute::SCOPE_GLOBAL,
            'required' => false,
            'user_defined' => true,
            'visible' => true,
            'used_in_product_listing' => true,
            'group' => 'General',
        ]);

        return $this;
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
