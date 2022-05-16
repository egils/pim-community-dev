<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Acceptance;

use Akeneo\Catalogs\Application\Persistence\FindOneCatalogByIdQueryInterface;
use Akeneo\Catalogs\Infrastructure\Persistence\UpsertCatalogQuery;
use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Public\CreateCatalogActionTest;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ApiContext implements Context
{
    private ContainerInterface $container;
    private ?Response $response;
    private ?KernelBrowser $client;

    public function __construct(
        KernelInterface $kernel,
        private AuthenticationContext $authentication,
    ) {
        $this->container = $kernel->getContainer()->get('test.service_container');
    }

    /**
     * @Given an existing catalog
     */
    public function anExistingCatalog()
    {
        $this->client ??= $this->authentication->createAuthenticatedClient([
            'read_catalogs',
            'write_catalogs',
            'delete_catalogs',
        ]);

        /** @var UserInterface $user */
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $commandBus = $this->container->get(CommandBus::class);
        $commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            $user->getId()
        ));
    }

    /**
     * @When the external application retrieves the catalog using the API
     */
    public function theExternalApplicationRetrievesTheCatalogUsingTheApi()
    {
        $this->client ??= $this->authentication->createAuthenticatedClient([
            'read_catalogs',
            'write_catalogs',
            'delete_catalogs',
        ]);

        $this->client->request(
            method: 'GET',
            uri: '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c',
        );

        $this->response = $this->client->getResponse();

        Assert::assertEquals(200, $this->response->getStatusCode());
    }

    /**
     * @Then the response should contain the catalog details
     */
    public function theResponseShouldContainTheCatalogDetails()
    {
        $payload = \json_decode($this->response->getContent(), true);

        Assert::assertArrayHasKey('id', $payload);
        Assert::assertArrayHasKey('name', $payload);
        Assert::assertArrayHasKey('enabled', $payload);
    }

    /**
     * @When the external application creates a catalog using the API
     */
    public function theExternalApplicationCreatesACatalogUsingTheApi()
    {
        $client = $this->authentication->createAuthenticatedClient([
            'read_catalogs',
            'write_catalogs',
            'delete_catalogs',
        ]);

        $client->request(
            method: 'POST',
            uri: '/api/rest/v1/catalogs',
            server: [
                'CONTENT_TYPE' => 'application/json',
            ],
            content: \json_encode([
                'name' => 'Store US',
            ]),
        );

        $this->response = $client->getResponse();

        Assert::assertEquals(201, $this->response->getStatusCode());
    }

    /**
     * @Then the response should contain the catalog id
     */
    public function theResponseShouldContainTheCatalogId()
    {
        $payload = \json_decode($this->response->getContent(), true);

        Assert::assertArrayHasKey('id', $payload);
    }

    /**
     * @Then the catalog should exist in the PIM
     */
    public function theCatalogShouldExistInThePim()
    {
        $payload = \json_decode($this->response->getContent(), true);

        $catalog = $this->container->get(FindOneCatalogByIdQueryInterface::class)
            ->execute($payload['id']);

        Assert::assertNotNull($catalog);
    }
}
