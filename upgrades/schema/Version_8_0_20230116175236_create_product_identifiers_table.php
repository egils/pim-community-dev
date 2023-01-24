<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_8_0_20230116175236_create_product_identifiers_table extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the pim_catalog_product_identifiers table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
            CREATE TABLE IF NOT EXISTS pim_catalog_product_identifiers(
                uuid BINARY(16) NOT NULL PRIMARY KEY,
                identifiers JSON DEFAULT NULL COMMENT '(DC2Type:json_array)',
                CONSTRAINT pim_catalog_product_identifiers_pim_catalog_product_uuid_fk
                    FOREIGN KEY (uuid) REFERENCES akeneo_pim.pim_catalog_product (uuid)
                        ON DELETE CASCADE,
                INDEX idx_identifiers ( (CAST(identifiers AS CHAR(255) array)) )
            );
            SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
