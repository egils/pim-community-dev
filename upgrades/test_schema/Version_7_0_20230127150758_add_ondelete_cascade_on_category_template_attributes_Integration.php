<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_7_0_20230127150758_add_ondelete_cascade_on_category_template_attributes_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private Connection $connection;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function test_it_adds_on_delete_cascade_to_template_fk_on_category_attributes_table(): void
    {
        $this->dropForeignKeyIfExists(
            'pim_catalog_category_attribute',
            'FK_ATTRIBUTE_template_uiid'
        );
        $this->reExecuteMigration($this->getMigrationLabel());
        Assert::assertTrue($this->foreignKeyHasDeleteCascade(
            'pim_catalog_category_attribute',
            'FK_ATTRIBUTE_template_uiid'
        ));
    }

    private function dropForeignKeyIfExists(string $table, string $foreignKey): void
    {
        try {
            $this->connection->executeQuery(sprintf('ALTER TABLE %s DROP FOREIGN KEY %s', $table, $foreignKey));
        } catch (Exception $e) {
            // does nothing if the foreign key did not exist
        }
    }

    private function getMigrationLabel(): string
    {
        $migration = (new \ReflectionClass($this))->getShortName();
        return str_replace(array('_Integration', 'Version'), '', $migration);
    }

    private function foreignKeyHasDeleteCascade(string $table, string $foreignKey): bool
    {
        $database = $this->connection->getDatabase();

        $query = <<<SQL
SELECT DELETE_RULE
FROM information_schema.REFERENTIAL_CONSTRAINTS
WHERE CONSTRAINT_NAME=:foreign_key
AND TABLE_NAME=:table
AND CONSTRAINT_SCHEMA=:database
SQL;

        $deleteRule = $this->connection->fetchOne($query, [
            'foreign_key' => $foreignKey,
            'table' => $table,
            'database' => $database,
        ]);

        return 'CASCADE' === $deleteRule;
    }
}
