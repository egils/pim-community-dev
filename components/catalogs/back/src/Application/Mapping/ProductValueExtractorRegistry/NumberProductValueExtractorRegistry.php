<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping\ProductValueExtractorRegistry;

use Akeneo\Catalogs\Application\Mapping\Exception\ProductValueExtractorNotFoundException;
use Akeneo\Catalogs\Application\Mapping\ProductValueExtractor\ProductValueExtractorInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type RawProduct from GetRawProductQueryInterface
 */
final class NumberProductValueExtractorRegistry
{
    /**
     * @param ProductValueExtractorInterface[] $extractors
     */
    public function __construct(
        private readonly array $extractors,
    ) {
        foreach ($this->extractors as $extractor) {
            if (!$extractor instanceof ProductValueExtractorInterface) {
                throw new \LogicException(
                    static::class . ' accepts only array of ' .
                    ProductValueExtractorInterface::class . ' as argument.'
                );
            }
        }
    }

    /**
     * @param RawProduct $product
     * @param array<string, mixed>|null $parameters
     */
    public function extract(
        array $product,
        string $code,
        string $attributeType,
        ?string $locale,
        ?string $scope,
        ?array $parameters,
    ): null | float | int {
        foreach ($this->extractors as $extractor) {
            if ($extractor->supports($attributeType)) {
                return $extractor->extract(
                    $product,
                    $code,
                    $locale,
                    $scope,
                    $parameters,
                );
            }
        }

        throw new ProductValueExtractorNotFoundException();
    }
}
