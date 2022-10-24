<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetCategoryTemplateByCategoryTree;
use Akeneo\Category\Application\Query\GetCategoryTreeByCategoryTemplate;
use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\Model\Template;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\PermissionCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryTreeByCategoryTemplateSql implements GetCategoryTreeByCategoryTemplate
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @param TemplateUuid $templateUuid
     * @return ?Category
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(TemplateUuid $templateUuid): ?Category
    {
        $query = <<< SQL
            SELECT * FROM pim_catalog_category category
                JOIN pim_catalog_category_tree_template category_tree_template
                     ON category_tree_template.category_tree_id=category.id
            WHERE template_uuid=:template_uuid
            ;
        SQL;

        $result = $this->connection->executeQuery(
            $query,
            [
                'template_uuid' => (string) $templateUuid
            ],
            [
                'template_uuid' => \PDO::PARAM_STR
            ]
        )->fetchAssociative();

        $category = null;

        if ($result) {
            $category = new Category(
                new CategoryId((int) $result['id']),
                new Code((string) $result['code']),
                LabelCollection::fromArray($result['labels']),
                new CategoryId((int) $result['parent_id']),
                new CategoryId((int) $result['root']),
                ValueCollection::fromArray($result['value_collection']),
                //TODO implement when permissions will be added
                null,
            );
        }

        return $category;
    }
}
