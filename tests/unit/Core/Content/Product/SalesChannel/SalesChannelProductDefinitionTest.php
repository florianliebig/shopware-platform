<?php declare(strict_types=1);

namespace Shopware\Tests\Unit\Core\Content\Product\SalesChannel;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Product\SalesChannel\ProductAvailableFilter;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Test\Generator;

/**
 * @internal
 */
#[CoversClass(SalesChannelProductDefinition::class)]
class SalesChannelProductDefinitionTest extends TestCase
{
    public function testProcessCriteriaRootLevel(): void
    {
        $definition = new SalesChannelProductDefinition();
        $criteria = new Criteria();
        $context = Generator::generateSalesChannelContext();

        $definition->processCriteria($criteria, $context);

        static::assertNotEmpty($criteria->getAssociations());
        static::assertTrue($criteria->hasAssociation('prices'));
        static::assertTrue($criteria->hasAssociation('unit'));
        static::assertTrue($criteria->hasAssociation('deliveryTime'));
        static::assertTrue($criteria->hasAssociation('cover'));

        static::assertNotEmpty($criteria->getFilters());
    }

    public function testProcessCriteriaAssociationLevel(): void
    {
        $definition = new SalesChannelProductDefinition();
        $criteria = new Criteria(nestingLevel: 1);
        $context = Generator::generateSalesChannelContext();

        $definition->processCriteria($criteria, $context);

        static::assertEmpty($criteria->getAssociations());

        static::assertNotEmpty($criteria->getFilters());
    }

    public function testProcessCriteriaWithExistingProductAvailableFilter(): void
    {
        $definition = new SalesChannelProductDefinition();
        $criteria = new Criteria();
        $context = Generator::generateSalesChannelContext();

        // Add existing ProductAvailableFilter
        $criteria->addFilter(new ProductAvailableFilter('sales-channel-id'));

        $filterCountBefore = count($criteria->getFilters());
        $definition->processCriteria($criteria, $context);
        $filterCountAfter = count($criteria->getFilters());

        // Should not add another availability filter
        static::assertEquals($filterCountBefore, $filterCountAfter);
    }

    public function testProcessCriteriaWithVisibilityFilter(): void
    {
        $definition = new SalesChannelProductDefinition();
        $criteria = new Criteria();
        $context = Generator::generateSalesChannelContext();

        // Add visibility filter
        $criteria->addFilter(new EqualsFilter('product.visibilities.salesChannelId', 'test-channel'));

        $filterCountBefore = count($criteria->getFilters());
        $definition->processCriteria($criteria, $context);
        $filterCountAfter = count($criteria->getFilters());

        // Should not add another availability filter due to existing visibility filter
        static::assertEquals($filterCountBefore, $filterCountAfter);
    }

    public function testProcessCriteriaWithNestedVisibilityFilter(): void
    {
        $definition = new SalesChannelProductDefinition();
        $criteria = new Criteria();
        $context = Generator::generateSalesChannelContext();

        // Add nested visibility filter in MultiFilter
        $multiFilter = new MultiFilter(MultiFilter::CONNECTION_AND, [
            new EqualsFilter('product.visibilities.visibility', 20),
            new EqualsFilter('product.active', true),
        ]);
        $criteria->addFilter($multiFilter);

        $filterCountBefore = count($criteria->getFilters());
        $definition->processCriteria($criteria, $context);
        $filterCountAfter = count($criteria->getFilters());

        // Should not add another availability filter due to existing visibility filter in MultiFilter
        static::assertEquals($filterCountBefore, $filterCountAfter);
    }
}
