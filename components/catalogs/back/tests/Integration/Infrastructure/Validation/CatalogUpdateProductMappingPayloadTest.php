<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation;

use Akeneo\Catalogs\Infrastructure\Validation\CatalogUpdateProductMappingPayload;
use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Command\UpdateProductMappingSchemaCommand;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\CatalogUpdateProductMappingPayloadValidator
 */
class CatalogUpdateProductMappingPayloadTest extends IntegrationTestCase
{
    private ?ValidatorInterface $validator;
    private ?CommandBus $commandBus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = self::getContainer()->get(ValidatorInterface::class);
        $this->commandBus = self::getContainer()->get(CommandBus::class);

        $this->purgeDataAndLoadMinimalCatalog();

        $this->createUser('admin', ['IT support'], ['ROLE_ADMINISTRATOR']);
        $this->commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            'admin',
        ));
        $this->commandBus->execute(new UpdateProductMappingSchemaCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            \json_decode($this->getValidSchemaData(), false, 512, JSON_THROW_ON_ERROR),
        ));
    }

    public function testItValidates(): void
    {
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
            'scopable' => false,
            'localizable' => false,
        ]);

        $violations = $this->validator->validate([
            'uuid' => [
                'source' => 'uuid',
                'scope' => null,
                'locale' => null,
            ],
            'name' => [
                'source' => 'name',
                'scope' => null,
                'locale' => null,
            ],
        ], new CatalogUpdateProductMappingPayload('db1079b6-f397-4a6a-bae4-8658e64ad47c_product.json'));

        $this->assertEmpty($violations);
    }

    public function testItReturnsViolationsWhenProductMappingIsNotAssociativeArray(): void
    {
        $violations = $this->validator->validate([
            [
                'source' => 'uuid',
                'scope' => null,
                'locale' => null,
            ],
        ], new CatalogUpdateProductMappingPayload('db1079b6-f397-4a6a-bae4-8658e64ad47c_product.json'));

        $this->assertViolationsListContains($violations, 'Invalid array structure.');
    }

    public function testItReturnsViolationsWhenSourceIsInvalid(): void {
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
            'scopable' => false,
            'localizable' => false,
        ]);

        $violations = $this->validator->validate([
            'uuid' => [
                'source' => 'unknown_attribute',
                'scope' => null,
                'locale' => null,
            ],
            'name' => [
                'source' => 'name',
                'scope' => null,
                'locale' => null,
            ]
        ], new CatalogUpdateProductMappingPayload('db1079b6-f397-4a6a-bae4-8658e64ad47c_product.json'));

        $this->assertViolationsListContains($violations, 'Invalid source value');
    }

    public function testItReturnsViolationsWhenTargetsAreMissing(): void {
        $violations = $this->validator->validate([], new CatalogUpdateProductMappingPayload('db1079b6-f397-4a6a-bae4-8658e64ad47c_product.json'));

        $this->assertViolationsListContains($violations, 'The mapping is incomplete, following targets are missing: "uuid", "name".');
    }

    public function testItReturnsViolationsWhenThereIsAdditionalTarget(): void {
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
            'scopable' => false,
            'localizable' => false,
        ]);

        $violations = $this->validator->validate([
            'uuid' => [
                'source' => 'uuid',
                'scope' => null,
                'locale' => null,
            ],
            'name' => [
                'source' => 'name',
                'scope' => null,
                'locale' => null,
            ],
            'additional' => [
                'source' => 'uuid',
                'scope' => null,
                'locale' => null,
            ]
        ], new CatalogUpdateProductMappingPayload('db1079b6-f397-4a6a-bae4-8658e64ad47c_product.json'));

        $this->assertViolationsListContains($violations, 'The mapping is incorrect, following targets don\'t exist: "additional".');
    }

    public function testItReturnsViolationsWhenSourceTypeIsIncorrect(): void {
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_number',
            'scopable' => false,
            'localizable' => false,
        ]);

        $violations = $this->validator->validate([
            'uuid' => [
                'source' => 'uuid',
                'scope' => null,
                'locale' => null,
            ],
            'name' => [
                'source' => 'name',
                'scope' => null,
                'locale' => null,
            ],
        ], new CatalogUpdateProductMappingPayload('db1079b6-f397-4a6a-bae4-8658e64ad47c_product.json'));

        $this->assertViolationsListContains($violations, 'The selected source type does not match the requirements: string expected.');
    }

    private function getValidSchemaData(): string
    {
        return <<<'JSON_WRAP'
        {
          "$id": "https://example.com/product",
          "$schema": "https://api.akeneo.com/mapping/product/0.0.2/schema",
          "$comment": "My first schema !",
          "title": "Product Mapping",
          "description": "JSON Schema describing the structure of products expected by our application",
          "type": "object",
          "properties": {
            "uuid": {
              "type": "string"
            },
            "name": {
              "type": "string"
            }
          }
        }
        JSON_WRAP;
    }
}
