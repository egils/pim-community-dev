<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductModelScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetUpToDateProductModelScoresQuery implements GetProductModelScoresQueryInterface
{
    public function __construct(
        private HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery,
        private GetProductModelScoresQueryInterface $getProductModelScoresQuery
    ) {
    }

    public function byProductModelId(ProductEntityIdInterface $productModelModelId): Read\Scores
    {
        if ($this->hasUpToDateEvaluationQuery->forEntityId($productModelModelId)) {
            return $this->getProductModelScoresQuery->byProductModelId($productModelModelId);
        }

        return new Read\Scores(new ChannelLocaleRateCollection(), new ChannelLocaleRateCollection());
    }

    public function byProductModelIdCollection(ProductEntityIdCollection $productIdCollection): array
    {
        $upToDateProducts = $this->hasUpToDateEvaluationQuery->forEntityIdCollection($productIdCollection);

        return is_null($upToDateProducts) ? [] : $this->getProductModelScoresQuery->byProductModelIdCollection($upToDateProducts);
    }
}
