<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetCategoriesByCodeQueryInterface
{
    /**
     * @param array<string> $categoryCodes
     * @return array<array-key, array{code: string, label: string, isLeaf: bool}>
     */
    public function execute(array $categoryCodes, string $locale = 'en_US'): array;
}
