<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TargetSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromString', ['sku']);
    }

    function it_is_a_target()
    {
        $this->shouldBeAnInstanceOf(Target::class);
    }

    function it_cannot_be_instantiated_with_an_empty_string()
    {
        $this->beConstructedThrough('fromString', ['']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_returns_a_target()
    {
        $this->asString()->shouldReturn('sku');
    }
}
