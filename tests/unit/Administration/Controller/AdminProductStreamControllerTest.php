<?php declare(strict_types=1);

namespace Shopware\Tests\Unit\Administration\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Administration\Controller\AdminProductStreamController;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\SalesChannel\ProductAvailableFilter;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\RequestCriteriaBuilder;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextServiceInterface;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\Test\Generator;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
#[Package('framework')]
#[CoversClass(AdminProductStreamController::class)]
class AdminProductStreamControllerTest extends TestCase
{
    private MockObject&RequestCriteriaBuilder $requestCriteriaBuilder;

    private MockObject&SalesChannelContextServiceInterface $salesChannelContextService;

    /** @var MockObject&SalesChannelRepository<ProductCollection> */
    private MockObject&SalesChannelRepository $salesChannelRepository;

    private MockObject&ProductDefinition $productDefinition;

    protected function setUp(): void
    {
        $this->productDefinition = $this->createMock(ProductDefinition::class);
        $this->salesChannelRepository = $this->createMock(SalesChannelRepository::class);
        $this->salesChannelContextService = $this->createMock(SalesChannelContextServiceInterface::class);
        $this->requestCriteriaBuilder = $this->createMock(RequestCriteriaBuilder::class);
    }

    public function testProductStreamPreview(): void
    {
        $context = Context::createDefaultContext();
        $controller = new AdminProductStreamController(
            $this->productDefinition,
            $this->salesChannelRepository,
            $this->salesChannelContextService,
            $this->requestCriteriaBuilder,
        );

        $collection = new ProductCollection();

        $this->salesChannelRepository->expects($this->once())->method('search')
            ->willReturn(new EntitySearchResult(
                'product',
                1,
                $collection,
                null,
                new Criteria(),
                $context
            ));

        $response = $controller->productStreamPreview('salesChannelId', new Request(), $context);
        static::assertNotFalse($response->getContent());
        static::assertJsonStringEqualsJsonString(
            '{"extensions":[],"elements":[],"aggregations":[],"page":1,"limit":null,"entity":"product","total":1,"states":[]}',
            $response->getContent()
        );
    }

    public function testProductStreamPreviewWithVisibilityFilter(): void
    {
        $context = Context::createDefaultContext();
        $salesChannelContext = Generator::generateSalesChannelContext();
        $criteria = new Criteria();
        // Add visibility filter to simulate product stream with price filter
        $criteria->addFilter(new EqualsFilter('product.visibilities.salesChannelId', 'test-channel'));

        $controller = new AdminProductStreamController(
            $this->productDefinition,
            $this->salesChannelRepository,
            $this->salesChannelContextService,
            $this->requestCriteriaBuilder,
        );

        $this->requestCriteriaBuilder->expects($this->once())
            ->method('handleRequest')
            ->willReturn($criteria);

        $this->salesChannelContextService->expects($this->once())
            ->method('get')
            ->willReturn($salesChannelContext);

        $collection = new ProductCollection();

        // Check that the criteria passed to search does not have additional ProductAvailableFilter
        $this->salesChannelRepository->expects($this->once())
            ->method('search')
            ->with($this->callback(function (Criteria $passedCriteria) {
                $productAvailableFilters = array_filter(
                    $passedCriteria->getFilters(),
                    fn($filter) => $filter instanceof ProductAvailableFilter
                );
                // Should have no ProductAvailableFilter since we already have visibility filter
                return count($productAvailableFilters) === 0;
            }))
            ->willReturn(new EntitySearchResult(
                'product',
                1,
                $collection,
                null,
                new Criteria(),
                $context
            ));

        $response = $controller->productStreamPreview('salesChannelId', new Request(), $context);
        static::assertNotFalse($response->getContent());
    }

    public function testProductStreamPreviewWithoutVisibilityFilter(): void
    {
        $context = Context::createDefaultContext();
        $salesChannelContext = Generator::generateSalesChannelContext();
        $criteria = new Criteria();
        // No visibility filter

        $controller = new AdminProductStreamController(
            $this->productDefinition,
            $this->salesChannelRepository,
            $this->salesChannelContextService,
            $this->requestCriteriaBuilder,
        );

        $this->requestCriteriaBuilder->expects($this->once())
            ->method('handleRequest')
            ->willReturn($criteria);

        $this->salesChannelContextService->expects($this->once())
            ->method('get')
            ->willReturn($salesChannelContext);

        $collection = new ProductCollection();

        // Check that the criteria passed to search has ProductAvailableFilter
        $this->salesChannelRepository->expects($this->once())
            ->method('search')
            ->with($this->callback(function (Criteria $passedCriteria) {
                $productAvailableFilters = array_filter(
                    $passedCriteria->getFilters(),
                    fn($filter) => $filter instanceof ProductAvailableFilter
                );
                // Should have ProductAvailableFilter since no visibility filter exists
                return count($productAvailableFilters) === 1;
            }))
            ->willReturn(new EntitySearchResult(
                'product',
                1,
                $collection,
                null,
                new Criteria(),
                $context
            ));

        $response = $controller->productStreamPreview('salesChannelId', new Request(), $context);
        static::assertNotFalse($response->getContent());
    }
}
