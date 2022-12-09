<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetElasticsearchProductModelProjectionInterface;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\ChunkProductModelCodes;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductModelIndexerInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;

/**
 * Indexer responsible for the indexation of product model entities. This indexer DOES NOT index children products that can
 * contain information about the parent product models, such as the inherited values from the parent product model.
 *
 * This indexer SHOULD NOT be used when you update a product model, as you have to update the parent document in Elasticsearch.
 *
 * Please use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelDescendantsAndAncestorsIndexer in that case.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelIndexer implements ProductModelIndexerInterface
{
    private const PRODUCT_MODEL_IDENTIFIER_PREFIX = 'product_model_';


    /** @see ProductIndexer::MEMORY_RATIO for the explanation of this ratio*/
    private const MEMORY_RATIO = 60;

    public function __construct(
        private Client $productAndProductModelClient,
        private GetElasticsearchProductModelProjectionInterface $getElasticsearchProductModelProjection,
        private ChunkProductModelCodes $chunkProductModelCodes,
        private PhpMemoryLimit $phpMemoryLimit
    ) {
    }

    /**
     * Indexes a product model in the product and product model index from its code.
     *
     * {@inheritdoc}
     */
    public function indexFromProductModelCode(string $productModelCode, array $options = []): void
    {
        $this->indexFromProductModelCodes([$productModelCode], $options);
    }

    /**
     * Indexes a list of product models in the product and product model index from their codes.
     *
     * {@inheritdoc}
     */
    public function indexFromProductModelCodes(array $productModelCodes, array $options = []): void
    {
        if (empty($productModelCodes)) {
            return;
        }

        $indexRefresh = $options['index_refresh'] ?? Refresh::disable();

        $maxMemoryPerChunk = (int) floor($this->phpMemoryLimit->asBytesFromPHPConfig() / self::MEMORY_RATIO);
        $chunks =$this->chunkProductModelCodes->byRawValuesSize($productModelCodes, $maxMemoryPerChunk);

        foreach ($chunks as $productModelCodesChunk) {
            $elasticsearchProductModelProjections =
                $this->getElasticsearchProductModelProjection->fromProductModelCodes($productModelCodesChunk);

            $normalizedProductModelProjections = (
                static function (iterable $projections): iterable {
                    foreach ($projections as $productModelCode => $projection) {
                        yield $productModelCode => $projection->toArray();
                    }
                }
            )($elasticsearchProductModelProjections);

            $this->productAndProductModelClient->bulkIndexes($normalizedProductModelProjections, 'id', $indexRefresh);
        }
    }

    /**
     * Removes the product model from the product and product model index.
     *
     * {@inheritdoc}
     */
    public function removeFromProductModelId(int $productModelId, array $options = []): void
    {
        $this->removeFromProductModelIds([$productModelId], $options);
    }

    /**
     * Removes the product models from the product and product model index.
     * Removes also the descendants of the product models (and the descendants of the descendants, etc...).
     *
     * {@inheritdoc}
     */
    public function removeFromProductModelIds(array $productModelIds, array $options = []): void
    {
        if (empty($productModelIds)) {
            return;
        }

        $indexIdentifiers = array_map(
            function ($productModelId) {
                return self::PRODUCT_MODEL_IDENTIFIER_PREFIX . (string) $productModelId;
            },
            $productModelIds
        );

        $this->productAndProductModelClient->deleteByQuery([
            'query' => [
                'bool' => [
                    'should' => [
                        ['terms' => ['id' => $indexIdentifiers]],
                        ['terms' => ['ancestors.ids' => $indexIdentifiers]],
                    ],
                ],
            ],
        ]);
    }

    private function memoryLimitAsBytes(): int
    {
        $limit = ini_get('memory_limit');
        if (false === $limit) {
            return 512 * 1024 * 1024;
        }

        if ($limit === '-1') {
            return 512 * 1024 * 1024 * 1024;
        }

        $lastCharacter = substr($limit, -1);
        if ($lastCharacter === 'K') {
            return  (int) substr($limit, 0, -1) * 1024;
        }
        if ($lastCharacter === 'M') {
            return  (int) substr($limit, 0, -1) * 1024 * 1024;
        }
        if ($lastCharacter === 'G') {
            return  (int) substr($limit, 0, -1) * 1024 * 1024 * 1024;
        }

        return (int) $limit;
    }
}
