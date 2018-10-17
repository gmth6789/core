<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Test\ORM\Search\Aggregation;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\ORM\RepositoryInterface;
use Shopware\Core\Framework\ORM\Search\Aggregation\CardinalityAggregation;
use Shopware\Core\Framework\ORM\Search\Aggregation\CardinalityAggregationResult;
use Shopware\Core\Framework\ORM\Search\Criteria;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;

class CardinalityAggregationTest extends TestCase
{
    use IntegrationTestBehaviour;

    /**
     * @var RepositoryInterface
     */
    private $taxRepository;

    /**
     * @var Connection
     */
    private $connection;

    protected function setUp()
    {
        $this->connection = $this->getContainer()->get(Connection::class);
        $this->taxRepository = $this->getContainer()->get('tax.repository');

        $this->connection->executeUpdate('DELETE FROM tax');
    }

    public function testCardinalityAggregation(): void
    {
        $context = Context::createDefaultContext(Defaults::TENANT_ID);
        $this->setupFixtures($context);

        $criteria = new Criteria();
        $criteria->addAggregation(new CardinalityAggregation('taxRate', 'rate_agg'));

        $result = $this->taxRepository->aggregate($criteria, $context);

        /** @var CardinalityAggregationResult $rateAgg */
        $rateAgg = $result->getAggregations()->get('rate_agg');
        static::assertNotNull($rateAgg);
        static::assertEquals(4, $rateAgg->getCardinality());
        static::assertEquals(['cardinality' => 4], $rateAgg->getResult());
    }

    private function setupFixtures(Context $context): void
    {
        $payload = [
            ['name' => 'Tax rate #1', 'taxRate' => 10],
            ['name' => 'Tax rate #2', 'taxRate' => 20],
            ['name' => 'Tax rate #3', 'taxRate' => 10],
            ['name' => 'Tax rate #4', 'taxRate' => 20],
            ['name' => 'Tax rate #5', 'taxRate' => 50],
            ['name' => 'Tax rate #6', 'taxRate' => 50],
            ['name' => 'Tax rate #7', 'taxRate' => 90],
            ['name' => 'Tax rate #8', 'taxRate' => 10],
        ];

        $this->taxRepository->create($payload, $context);
    }
}
