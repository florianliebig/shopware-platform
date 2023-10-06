<?php declare(strict_types=1);

namespace Shopware\Tests\Unit\Core\Content\ProductExport;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\ProductExport\Exception\RenderFooterException;
use Shopware\Core\Content\ProductExport\Exception\RenderHeaderException;
use Shopware\Core\Content\ProductExport\Exception\RenderProductException;
use Shopware\Core\Content\ProductExport\ProductExportException;
use Shopware\Core\Test\Annotation\DisabledFeatures;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 *
 * @covers \Shopware\Core\Content\ProductExport\ProductExportException
 */
class ProductExportExceptionTest extends TestCase
{
    public function testTemplateBodyNotSet(): void
    {
        $exception = ProductExportException::templateBodyNotSet();
        static::assertSame(Response::HTTP_BAD_REQUEST, $exception->getStatusCode());
        static::assertSame('PRODUCT_EXPORT__TEMPLATE_BODY_NOT_SET', $exception->getErrorCode());

        static::expectException(ProductExportException::class);

        throw $exception;
    }

    /**
     * @DisabledFeatures(features={"v6.6.0.0"})
     */
    public function testRenderFooterException(): void
    {
        $exception = ProductExportException::renderFooterException('Footer!');
        static::assertSame('Failed rendering string template using Twig: Footer!', $exception->getMessage());

        static::expectException(RenderFooterException::class);

        throw $exception;
    }

    /**
     * @DisabledFeatures(features={"v6.6.0.0"})
     */
    public function testRenderHeaderException(): void
    {
        $exception = ProductExportException::renderHeaderException('Header!');
        static::assertSame('Failed rendering string template using Twig: Header!', $exception->getMessage());

        static::expectException(RenderHeaderException::class);

        throw $exception;
    }

    /**
     * @DisabledFeatures(features={"v6.6.0.0"})
     */
    public function testRenderProductException(): void
    {
        $exception = ProductExportException::renderProductException('Product!');
        static::assertSame('Failed rendering string template using Twig: Product!', $exception->getMessage());

        static::expectException(RenderProductException::class);

        throw $exception;
    }

    public function testRenderFooterException660(): void
    {
        $exception = ProductExportException::renderFooterException('Footer!');
        static::assertSame('Failed rendering string template using Twig: Footer!', $exception->getMessage());

        static::expectException(ProductExportException::class);

        throw $exception;
    }

    public function testRenderHeaderException660(): void
    {
        $exception = ProductExportException::renderHeaderException('Header!');
        static::assertSame('Failed rendering string template using Twig: Header!', $exception->getMessage());

        static::expectException(ProductExportException::class);

        throw $exception;
    }

    public function testRenderProductException660(): void
    {
        $exception = ProductExportException::renderProductException('Product!');
        static::assertSame('Failed rendering string template using Twig: Product!', $exception->getMessage());

        static::expectException(ProductExportException::class);

        throw $exception;
    }
}
