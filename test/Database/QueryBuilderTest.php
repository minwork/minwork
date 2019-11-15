<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace Test\Database;

use Minwork\Database\Builders\Select;
use Minwork\Database\Interfaces\ColumnInterface;
use Minwork\Database\Interfaces\TableInterface;
use Minwork\Database\Utility\Query;
use Minwork\Database\Utility\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Test\Utils\DatabaseProvider;
use Throwable;

class QueryBuilderTest extends TestCase
{
    /**
     * @var QueryBuilder
     */
    private $qb;

    /**
     * @var TableInterface
     */
    private static $table;

    public static function setUpBeforeClass()
    {
        // Create proper storage
        $table = DatabaseProvider::getTable('qb_builder_test');
        $table->setColumns([
            DatabaseProvider::createColumn('id', ColumnInterface::TYPE_INTEGER, null, false, true, true),
            DatabaseProvider::createColumn('name', ColumnInterface::TYPE_STRING),
            DatabaseProvider::createColumn('data', ColumnInterface::TYPE_TEXT),
            DatabaseProvider::createColumn('created', ColumnInterface::TYPE_DATETIME),
            DatabaseProvider::createColumn('active', ColumnInterface::TYPE_BOOLEAN),
            DatabaseProvider::createColumn('related_id', ColumnInterface::TYPE_INTEGER, null, true),
        ]);

        $table->create();

        self::$table = $table;
    }

    public static function tearDownAfterClass()
    {
        self::$table->remove();
    }

    protected function setUp()
    {
        $this->qb = new QueryBuilder(self::$table);
    }

    public function queryPartsProvider(): array
    {
        return [
            [
                // Conditions
                [
                    'name' => 'test',
                    'active' => true,
                    'related_id' => null,
                ],
                // Columns
                ['data' => 'json'],
                // Order
                ['created' => -1],
                // Limit
                3,
                // Group
                'related_id',
            ]
        ];
    }

    /**
     * @param $conditions
     * @param $columns
     * @param $order
     * @param $limit
     * @param $group
     *
     * @dataProvider queryPartsProvider
     */
    public function testBuildingSelectStatement($conditions, $columns, $order, $limit, $group)
    {
        $query = new Query($conditions, $columns, $limit, $order, $group);

        $select = $this->qb->select($query);

        $this->assertInstanceOf(Select::class, $select);

        $sql = $select->getSql(false);
        $this->assertInternalType('string', $sql);

        try {
            $this->qb->getStorage()->getDatabase()->exec($sql);
        } catch (Throwable $exception) {
            $this->fail("Query {$sql} didn't executed properly: {$exception}");
        }

        $sql = $select->getSql(true);
        $this->assertInternalType('string', $sql);

        try {
            $this->qb->getStorage()->getDatabase()->prepare($sql);
        } catch (Throwable $exception) {
            $this->fail("Query {$sql} didn't executed properly: {$exception}");
        }
    }
}