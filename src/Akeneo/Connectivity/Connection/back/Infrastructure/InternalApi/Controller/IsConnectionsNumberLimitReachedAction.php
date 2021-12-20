<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\InternalApi\Controller;

use Akeneo\Connectivity\Connection\Application\Settings\Query\IsConnectionsNumberLimitReachedHandler;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsConnectionsNumberLimitReachedAction
{
    public function __construct(private IsConnectionsNumberLimitReachedHandler $isConnectionsNumberLimitReachedHandler)
    {
    }

    public function __invoke()
    {
        return new JsonResponse(['limit_reached' => $this->isConnectionsNumberLimitReachedHandler->execute()]);
    }
}
