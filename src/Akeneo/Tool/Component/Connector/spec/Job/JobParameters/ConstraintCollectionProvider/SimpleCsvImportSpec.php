<?php

namespace spec\Akeneo\Tool\Component\Connector\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use PhpSpec\ObjectBehavior;

class SimpleCsvImportSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['my_supported_job_name']);
    }

    function it_is_a_provider()
    {
        $this->shouldImplement(ConstraintCollectionProviderInterface::class);
    }

    function it_provides_constraints_collection()
    {
        $collection = $this->getConstraintCollection();
        $collection->shouldReturnAnInstanceOf('Symfony\Component\Validator\Constraints\Collection');
        $fields = $collection->fields;
        $fields->shouldHaveCount(8);
        $fields->shouldHaveKey('storage');
        $fields->shouldHaveKey('delimiter');
        $fields->shouldHaveKey('enclosure');
        $fields->shouldHaveKey('withHeader');
        $fields->shouldHaveKey('escape');
        $fields->shouldHaveKey('invalid_items_file_format');
        $fields->shouldHaveKey('user_to_notify');
        $fields->shouldHaveKey('is_user_authenticated');
    }

    function it_supports_a_job(JobInterface $job)
    {
        $job->getName()->willReturn('my_supported_job_name');
        $this->supports($job)->shouldReturn(true);
    }
}
