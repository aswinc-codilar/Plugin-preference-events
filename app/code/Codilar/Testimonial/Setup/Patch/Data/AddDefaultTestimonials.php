<?php
declare(strict_types=1);

namespace Codilar\Testimonial\Setup\Patch\Data;

use Codilar\Testimonial\Model\ResourceModel\Testimonial as TestimonialResource;
use Codilar\Testimonial\Model\Testimonial;
use Codilar\Testimonial\Model\TestimonialFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

/**
 * Seeds the custom_testimonial table with default testimonials on setup:upgrade.
 */
class AddDefaultTestimonials implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * Testimonials to insert. Add or edit rows here.
     */
    private const TESTIMONIALS = [
        [
            Testimonial::NAME => 'Priya Nair',
            Testimonial::CITY => 'Bengaluru',
            Testimonial::RATING => 5,
            Testimonial::MESSAGE => 'Fast delivery and the product quality exceeded my expectations. Highly recommended!',
        ],
        [
            Testimonial::NAME => 'Ahmed Khan',
            Testimonial::CITY => 'Dubai',
            Testimonial::RATING => 4,
            Testimonial::MESSAGE => 'Great customer support team. They resolved my query within a few hours.',
        ],
        [
            Testimonial::NAME => 'Rahul Menon',
            Testimonial::CITY => 'Kochi',
            Testimonial::RATING => 5,
            Testimonial::MESSAGE => 'Smooth checkout experience and easy returns. I will definitely shop here again.',
        ],
    ];

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param TestimonialFactory $testimonialFactory
     * @param TestimonialResource $testimonialResource
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly TestimonialFactory $testimonialFactory,
        private readonly TestimonialResource $testimonialResource
    ) {
    }

    /**
     * @inheritdoc
     */
    public function apply(): self
    {
        $connection = $this->moduleDataSetup->getConnection();
        $connection->startSetup();

        foreach (self::TESTIMONIALS as $data) {
            $testimonial = $this->testimonialFactory->create();
            $testimonial->setData($data);
            $this->testimonialResource->save($testimonial);
        }

        $connection->endSetup();

        return $this;
    }

    /**
     * Remove the testimonials this patch inserted (used by module:uninstall).
     *
     * @return void
     */
    public function revert(): void
    {
        $connection = $this->moduleDataSetup->getConnection();
        $table = $this->moduleDataSetup->getTable(TestimonialResource::TABLE_NAME);

        foreach (self::TESTIMONIALS as $data) {
            $connection->delete($table, [
                Testimonial::NAME . ' = ?' => $data[Testimonial::NAME],
                Testimonial::MESSAGE . ' = ?' => $data[Testimonial::MESSAGE],
            ]);
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
