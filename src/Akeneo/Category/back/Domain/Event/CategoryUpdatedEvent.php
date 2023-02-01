<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\Event;

use Akeneo\Category\Domain\Model\Enrichment\Category;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CategoryUpdatedEvent
{
    /**
     * @param array<string, mixed> $changeset
     */
    public function __construct(
        private readonly Category $category,
        private readonly array $changeset,
    ) {
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * @return array<string, mixed>
     */
    public function getChangeset(): array
    {
        return $this->changeset;
    }
}
