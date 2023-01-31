<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor;

use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type RawProduct from GetRawProductQueryInterface
 */
interface ValueExtractorInterface
{
    public const SOURCE_TYPE_ATTRIBUTE_DATE = 'pim_catalog_date';
    public const SOURCE_TYPE_ATTRIBUTE_NUMBER = 'pim_catalog_number';
    public const SOURCE_TYPE_ATTRIBUTE_SIMPLE_SELECT = 'pim_catalog_simpleselect';
    public const SOURCE_TYPE_ATTRIBUTE_TEXT = 'pim_catalog_text';
    public const SOURCE_TYPE_ATTRIBUTE_TEXTAREA = 'pim_catalog_textarea';

    public const SOURCE_TYPE_FAMILY = 'family';

    public const TARGET_TYPE_NUMBER = 'number';
    public const TARGET_TYPE_STRING = 'string';

    public const TARGET_FORMAT_DATETIME = 'date-time';

    /**
     * @param RawProduct $product
     * @param array<string, mixed>|null $parameters
     */
    public function extract(
        array $product,
        string $code,
        ?string $locale,
        ?string $scope,
        ?array $parameters,
    ): mixed;

    public function getSupportedSourceType(): string;

    public function getSupportedTargetType(): string;

    public function getSupportedTargetFormat(): ?string;
}
