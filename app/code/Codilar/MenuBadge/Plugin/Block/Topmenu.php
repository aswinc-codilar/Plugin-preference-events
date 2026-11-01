<?php
declare(strict_types=1);

namespace Codilar\MenuBadge\Plugin\Block;

use Codilar\MenuBadge\Model\Category\Attribute\Source\MenuBadge;
use Codilar\MenuBadge\Setup\Patch\Data\AddMenuBadgeCategoryAttribute;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Data\Tree\Node;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Block\Html\Topmenu as TopmenuBlock;

/**
 * Adds a "menu-badge menu-badge-<value>" CSS class to category menu nodes that
 * have a Menu Badge set; the badge itself is drawn by the module's stylesheet.
 */
class Topmenu
{
    private const NODE_ID_PREFIX = 'category-node-';

    public function __construct(
        private readonly CollectionFactory $collectionFactory,
        private readonly StoreManagerInterface $storeManager,
        private readonly MenuBadge $menuBadgeSource
    ) {
    }

    public function beforeGetHtml(
        TopmenuBlock $subject,
        $outermostClass = '',
        $childrenWrapClass = '',
        $limit = 0
    ): void {
        $nodes = [];
        foreach ($subject->getMenu()->getAllChildNodes() as $node) {
            $nodeId = (string) $node->getId();
            if (str_starts_with($nodeId, self::NODE_ID_PREFIX)) {
                $nodes[(int) substr($nodeId, strlen(self::NODE_ID_PREFIX))] = $node;
            }
        }
        if (!$nodes) {
            return;
        }

        foreach ($this->getBadges(array_keys($nodes)) as $categoryId => $badge) {
            /** @var Node $node */
            $node = $nodes[$categoryId];
            $node->setClass(trim($node->getClass() . ' menu-badge menu-badge-' . $badge));
        }
    }

    /**
     * @param int[] $categoryIds
     * @return array<int, string> category ID => badge value, only for categories with a badge
     */
    private function getBadges(array $categoryIds): array
    {
        $allowed = array_filter(array_column($this->menuBadgeSource->getAllOptions(), 'value'));

        $collection = $this->collectionFactory->create();
        $collection->setStoreId((int) $this->storeManager->getStore()->getId());
        $collection->addAttributeToSelect(AddMenuBadgeCategoryAttribute::ATTRIBUTE_CODE);
        $collection->addIdFilter($categoryIds);

        $badges = [];
        foreach ($collection as $category) {
            $badge = (string) $category->getData(AddMenuBadgeCategoryAttribute::ATTRIBUTE_CODE);
            if (in_array($badge, $allowed, true)) {
                $badges[(int) $category->getId()] = $badge;
            }
        }

        return $badges;
    }
}
