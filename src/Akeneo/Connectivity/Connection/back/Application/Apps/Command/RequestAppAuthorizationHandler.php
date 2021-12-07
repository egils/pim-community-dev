<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequest;
use Akeneo\Connectivity\Connection\Domain\Apps\ScopeFilterInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RequestAppAuthorizationHandler
{
    private ValidatorInterface $validator;
    private AppAuthorizationSessionInterface $session;
    private ScopeFilterInterface $scopeFilter;
    private GetAppQueryInterface $getAppQuery;

    public function __construct(
        ValidatorInterface $validator,
        AppAuthorizationSessionInterface $session,
        ScopeFilterInterface $scopeFilter,
        GetAppQueryInterface $getAppQuery
    ) {
        $this->validator = $validator;
        $this->session = $session;
        $this->scopeFilter = $scopeFilter;
        $this->getAppQuery = $getAppQuery;
    }

    public function handle(RequestAppAuthorizationCommand $command): void
    {
        $violations = $this->validator->validate($command);
        if (\count($violations) > 0) {
            throw new InvalidAppAuthorizationRequest($violations);
        }

        $app = $this->getAppQuery->execute($command->getClientId());
        if (null === $app) {
            throw new \ErrorException('App should exists when validating the authorization wizard');
        }

        $allowedScopes = $this->scopeFilter->filterAllowedScopes(ScopeList::fromScopeString($command->getScope()));

        $authorization = AppAuthorization::createFromRequest(
            $command->getClientId(),
            $allowedScopes->toScopeString(),
            $app->getCallbackUrl(),
            $command->getState(),
        );

        $this->session->initialize($authorization);
    }
}
