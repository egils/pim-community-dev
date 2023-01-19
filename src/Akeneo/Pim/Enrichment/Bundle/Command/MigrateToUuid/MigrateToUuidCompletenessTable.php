<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\Utils\StatusAwareTrait;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

/**
 * The process is different for completeness
 * Creating a temporary table without indexes and foreign keys allows us to speed up the process
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MigrateToUuidCompletenessTable implements MigrateToUuidStep
{
    use MigrateToUuidTrait;
    use StatusAwareTrait;

    const TABLE_NAME = 'pim_catalog_completeness';
    const TEMP_TABLE_NAME = 'pim_catalog_completeness_temp';
    const INSERT_BATCH_SIZE = 100000;

    public function __construct(private Connection $connection, private LoggerInterface $logger)
    {
    }

    public function getDescription(): string
    {
        return 'Migrates the completeness table';
    }

    public function getName(): string
    {
        return 'migrate_completeness_table';
    }

    public function shouldBeExecuted(): bool
    {
        return 0 < $this->getMissingForeignUuidCount(
            'pim_catalog_completeness',
            'product_uuid',
            'product_id'
        );
    }

    public function getMissingCount(): int
    {
        return $this->getMissingForeignUuidCount(
            'pim_catalog_completeness',
            'product_uuid',
            'product_id'
        );
    }

    private function getMissingForeignUuidCount(string $tableName, string $uuidColumnName, string $idColumnName): int
    {
        if (!$this->tableExists($tableName)) {
            return 0;
        }

        return $this->getNullForeignUuidCellsCount($tableName, $uuidColumnName, $idColumnName);
    }

    public function addMissing(Context $context): bool
    {
        $logContext = $context->logContext;
        if($context->dryRun()) {
            return true;
        }

        $this->logger->notice(sprintf('Will create the temporary completeness table'), $logContext->toArray());
        $this->connection->executeQuery(<<<SQL
            CREATE TABLE :temp_table_name (SELECT * FROM :original_table_name WHERE 1 = 0)
        SQL,
            [
                'temp_table_name' => self::TEMP_TABLE_NAME,
                'original_table_name' => self::TABLE_NAME
            ]
        );

        // Add primary key to speed up the lookup during inserts
        $this->logger->notice(sprintf('Will set the primary key'), $logContext->toArray());
        $this->connection->executeQuery(<<<SQL
            ALTER TABLE :temp_table_name ADD PRIMARY KEY (id)
        SQL,
            ['temp_table_name' => self::TEMP_TABLE_NAME]
        );

        // Insert uuids
        $this->logger->notice(sprintf('Will insert data into temporary table'), $logContext->toArray());
        do {
            $count = $this->connection->executeQuery(<<<SQL
                    INSERT INTO :temp_table_name
                    SELECT c.id, c.locale_id, c.channel_id, c.missing_count, c.required_count, p.uuid as product_uuid
                    FROM :table_name c
                    JOIN pim_catalog_product p on c.product_id = p.id
                    WHERE c.id > :max_migrated_id
                    LIMIT :batch_size;
                SQL,
                [
                    'temp_table_name' => self::TEMP_TABLE_NAME,
                    'table_name' => self::TABLE_NAME,
                    'max_migrated_id' => $this->getMaxMigratedId() ?? 0,
                    'batch_size' => self::INSERT_BATCH_SIZE
                ]
            )->rowCount();
        } while ($count > 0);

        // Put index on the uuid column
        $this->logger->notice(sprintf('Will index on the temporary completeness table uuid column'), $logContext->toArray());
        $this->connection->executeQuery(<<<SQL
            CREATE INDEX product_uuid ON :temp_table_name (product_uuid)
        SQL,
            ['temp_table_name' => self::TEMP_TABLE_NAME]
        );

        // Set uuids as not nullable
        $this->logger->notice(sprintf('Will set the uuid column as not nullable'), $logContext->toArray());
        $this->connection->executeQuery(<<<SQL
            ALTER TABLE :table_name
            MODIFY `product_uuid` BINARY(16) NOT NULL;
        SQL,
            [
                'table_name' => self::TABLE_NAME
            ]
        );

        // Drop original table
        $this->logger->notice(sprintf('Will drop the original table'), $logContext->toArray());
        $this->connection->executeQuery(<<<SQL
            DROP TABLE :table_name;
        SQL,
            ['table_name' => self::TABLE_NAME]
        );

        // Replace with temporary table which is now ready
        $this->logger->notice(sprintf('Will rename the temporary table'), $logContext->toArray());
        $this->connection->executeQuery(<<<SQL
            RENAME TABLE :temp_table_name TO :table_name;
        SQL,
            [
                'temp_table_name' => self::TEMP_TABLE_NAME,
                'table_name' => self::TABLE_NAME
            ]
        );

        // Temporarily disable foreign key checks
        $this->logger->notice(sprintf('Will disable foreign key checks'), $logContext->toArray());
        $this->connection->executeQuery(<<<SQL
            SET FOREIGN_KEY_CHECKS=0
        SQL
        );

        // Add channel foreign key
        $this->logger->notice(sprintf('Will add foreign key towards channels'), $logContext->toArray());
        $this->connection->executeQuery(<<<SQL
            ALTER TABLE :temp_table_name
                ADD CONSTRAINT `FK_113BA85472F5A1AA` FOREIGN KEY (`channel_id`)
                REFERENCES `pim_catalog_channel` (`id`)
                ON DELETE CASCADE
                ON UPDATE RESTRICT
        SQL,
            ['temp_table_name' => self::TEMP_TABLE_NAME]
        );

        // Add locale foreign key
        $this->logger->notice(sprintf('Will add foreign key towards locales'), $logContext->toArray());
        $this->connection->executeQuery(<<<SQL
            ALTER TABLE :temp_table_name
                ADD CONSTRAINT `FK_113BA854E559DFD1` FOREIGN KEY (`locale_id`)
                REFERENCES `pim_catalog_locale` (`id`)
                ON DELETE CASCADE
                ON UPDATE RESTRICT
        SQL,
            ['temp_table_name' => self::TEMP_TABLE_NAME]
        );

        // Temporarily disable unique checks
        $this->logger->notice(sprintf('Will disable unique checks'), $logContext->toArray());
        $this->connection->executeQuery(<<<SQL
            SET UNIQUE_CHECKS=0
        SQL
        );

        $this->logger->notice(sprintf('Will create unique constraint'), $logContext->toArray());
        $this->connection->executeQuery(<<<SQL
            ALTER TABLE :temp_table_name
                ADD CONSTRAINT `channel_locale_product_unique_idx` UNIQUE (`channel_id`,`locale_id`,`product_uuid`)
        SQL,
            ['temp_table_name' => self::TEMP_TABLE_NAME]
        );

        // Re-enable checks
        $this->logger->notice(sprintf('Will re-enable checks'), $logContext->toArray());
        $this->connection->executeQuery(<<<SQL
            SET UNIQUE_CHECKS=1
        SQL
        );
        $this->connection->executeQuery(<<<SQL
            SET FOREIGN_KEY_CHECKS=1
        SQL
        );

        return true;
    }

    private function getNullForeignUuidCellsCount(string $tableName, string $uuidColumnName, string $idColumnName): int
    {
        $sql = <<<SQL
            SELECT COUNT(*)
            FROM {table_name}
            INNER JOIN pim_catalog_product p ON p.id = {table_name}.{id_column_name}
            WHERE {table_name}.{uuid_column_name} IS NULL
            {extra_condition}
        SQL;

        $query = \strtr($sql, [
            '{table_name}' => $tableName,
            '{uuid_column_name}' => $uuidColumnName,
            '{id_column_name}' => $idColumnName,
            '{extra_condition}' => \in_array($tableName, ['pim_versioning_version', 'pim_comment_comment']) ?
                ' AND resource_name = "Akeneo\\\Pim\\\Enrichment\\\Component\\\Product\\\Model\\\Product"' :
                ''
        ]);

        return (int) $this->connection->fetchOne($query);
    }

    private function getMaxMigratedId() {
        return $this->connection->executeQuery(<<<SQL
            SELECT MAX(id) from :temp_table_name
        SQL,
            ['temp_table_name' => self::TEMP_TABLE_NAME]
        )->fetchOne();
    }
}
