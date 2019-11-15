<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace Test;

use Exception;
use Minwork\Database\Interfaces\ColumnInterface;
use Minwork\Database\Interfaces\DatabaseInterface;
use Minwork\Database\Interfaces\TableInterface;
use Minwork\Database\Utility\Condition;
use Minwork\Helper\DateHelper;
use Minwork\Helper\Formatter;
use Minwork\Helper\Random;
use PDO;
use PHPUnit\Framework\TestCase;
use Test\Utils\DatabaseProvider;
use Throwable;

class DatabaseTest extends TestCase
{

    const TABLE_NAME = '#!@$%^&*()_+;/,`~<>:"\'}{|\\';


    public function databaseProvider()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return [
            [
                DatabaseProvider::getDatabase(DatabaseProvider::TYPE_MYSQL),
                DatabaseProvider::getTableClass(DatabaseProvider::TYPE_MYSQL),
                DatabaseProvider::getColumnClass(DatabaseProvider::TYPE_MYSQL)
            ],
            [
                DatabaseProvider::getDatabase(DatabaseProvider::TYPE_SQLITE),
                DatabaseProvider::getTableClass(DatabaseProvider::TYPE_SQLITE),
                DatabaseProvider::getColumnClass(DatabaseProvider::TYPE_SQLITE)
            ]
        ];
    }

    /**
     * @param DatabaseInterface $database
     * @param string $tableClass
     * @param string $columnClass
     *
     * @dataProvider databaseProvider
     */
    public function testDatabase(DatabaseInterface $database, string $tableClass, string $columnClass)
    {
        /** @var TableInterface $table */
        $table = new $tableClass($database, self::TABLE_NAME, [
            new $columnClass(TableInterface::DEFAULT_PK_FIELD, ColumnInterface::TYPE_INTEGER, null, false, true),
            new $columnClass('data', ColumnInterface::TYPE_STRING, 'test'),
            new $columnClass('date', ColumnInterface::TYPE_DATETIME, null, true)
        ]);

        // Test data
        $data = [
            TableInterface::DEFAULT_PK_FIELD => PHP_INT_MIN,
            'date' => null
        ];
        $data2 = [
            TableInterface::DEFAULT_PK_FIELD => PHP_INT_MAX,
            TableInterface::DEFAULT_PK_FIELD . '2' => Random::string(10),
            'data2' => Random::string(511)
        ];
        $data3 = [
            TableInterface::DEFAULT_PK_FIELD => 0,
            'data' => '',
            'date' => null
        ];
        $updateData = [
            TableInterface::DEFAULT_PK_FIELD => rand(0, PHP_INT_MAX),
            'data' => Random::string(255),
            'date' => DateHelper::addDays(2)
        ];

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertTrue($table->create(true));

        // Basic check on empty table
        $this->assertEquals(false, $table->exists($data));
        $this->assertEquals(0, $table->countRows());
        $this->assertEquals(0, $table->countRows($data));
        $this->assertEquals(Formatter::removeQuotes(self::TABLE_NAME), $table->getName(false));
        $this->assertEquals(TableInterface::DEFAULT_PK_FIELD, $table->getPrimaryKey());

        // Insert
        $table->insert($data);
        $this->assertEquals(true, $table->exists([
            TableInterface::DEFAULT_PK_FIELD => $data[TableInterface::DEFAULT_PK_FIELD]
        ]));
        $this->assertEquals(1, $table->countRows());
        $this->assertEquals(1, $table->countRows($data));

        $this->assertEquals($data, $table->select($data, array_keys($data))
            ->fetch(PDO::FETCH_ASSOC));

        // Check if data column has its default value
        $this->assertEquals([
            'data' => $table->getColumns()['data']->getDefaultValue()
        ], $table->select([
            TableInterface::DEFAULT_PK_FIELD => $data[TableInterface::DEFAULT_PK_FIELD]
        ], [
            'data'
        ])
            ->fetch(PDO::FETCH_ASSOC));
        // Check if insert data with default value from schema is same as table data
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertSame($table->format($data, true), $table->format($table->select([
            TableInterface::DEFAULT_PK_FIELD => $data[TableInterface::DEFAULT_PK_FIELD]
        ], [
            TableInterface::DEFAULT_PK_FIELD,
            'date',
            'data'
        ])
            ->fetch(PDO::FETCH_ASSOC)));

        // Update
        $table->update($updateData, [
            TableInterface::DEFAULT_PK_FIELD => $data[TableInterface::DEFAULT_PK_FIELD]
        ]);
        $this->assertEquals($updateData, $table->select([
            TableInterface::DEFAULT_PK_FIELD => $updateData[TableInterface::DEFAULT_PK_FIELD]
        ], array_keys($updateData), [
            'date' => -1
        ], 1, TableInterface::DEFAULT_PK_FIELD)
            ->fetch(PDO::FETCH_ASSOC));

        // Insert another row and check condition building
        $table->insert($data3);

        $conditions = (new Condition())->column(TableInterface::DEFAULT_PK_FIELD)
            ->in([
                $updateData[TableInterface::DEFAULT_PK_FIELD],
                0
            ])
            ->and()
            ->column(TableInterface::DEFAULT_PK_FIELD)
            ->gte(0)
            ->and()
            ->condition((new Condition())->column('date')
                ->isNull()
                ->or()
                ->column('date')
                ->between(DateHelper::now(), DateHelper::addDays(20)))
            ->and()
            ->column('data')
            ->isNotNull();

        $result = $table->select($conditions, TableInterface::COLUMNS_ALL, [
            'date' => -1,
            TableInterface::DEFAULT_PK_FIELD => 1,
            'data' => 'DESC'
        ], 2, TableInterface::DEFAULT_PK_FIELD)->fetchAll(PDO::FETCH_ASSOC);

        $this->assertEquals(2, count($result));
        $this->assertEquals($updateData[TableInterface::DEFAULT_PK_FIELD], $result[0][TableInterface::DEFAULT_PK_FIELD]);
        $this->assertEquals($data3[TableInterface::DEFAULT_PK_FIELD], $result[1][TableInterface::DEFAULT_PK_FIELD]);

        // Clean up table
        $table->clear();
        $this->assertEquals(0, $table->countRows());

        // Change table schema
        /** @var ColumnInterface $secondPkColumn */
        $secondPkColumn = new $columnClass(TableInterface::DEFAULT_PK_FIELD . '2', ColumnInterface::TYPE_STRING, null, false, true);
        $secondPkColumn->setLength(10);

        $columns = $table->getColumns();
        $columns = array_merge(array_slice($columns, 0, 1, true), [
            strval($secondPkColumn) => $secondPkColumn
        ], array_slice($columns, 1, count($columns) - 1, true));
        unset($columns['date']);

        /** @var ColumnInterface $newDataColumn */
        $newDataColumn = new $columnClass('data2', ColumnInterface::TYPE_STRING);
        $newDataColumn->setLength(511);

        $columns['data'] = $newDataColumn;
        $table->setColumns($columns);
        $this->assertTrue($table->synchronize());

        $this->assertEquals(array_keys($data2), $table->getColumns(TableInterface::COLUMN_NAMES));
        $this->assertEquals(0, $table->countRows());
        $this->assertEquals([
            TableInterface::DEFAULT_PK_FIELD,
            TableInterface::DEFAULT_PK_FIELD . '2'
        ], $table->getPrimaryKey());

        $table->insert($data2);

        $currentData = $table->select([
            TableInterface::DEFAULT_PK_FIELD => $data2[TableInterface::DEFAULT_PK_FIELD],
            TableInterface::DEFAULT_PK_FIELD . '2' => $data2[TableInterface::DEFAULT_PK_FIELD . '2']
        ])->fetch(PDO::FETCH_ASSOC);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertSame($data2, $table->format($currentData));
        $this->assertTrue($table->remove());
    }

    /**
     * @param DatabaseInterface $database
     *
     * @dataProvider databaseProvider
     */
    public function testTransactions(DatabaseInterface $database)
    {
        $database->beginTransaction(); // 1
        $this->assertTrue($database->inTransaction());

        $database->beginTransaction(); // 2
        $database->beginTransaction(); // 3
        $database->beginTransaction(); // 4

        $database->rollBack(); // 4
        $this->assertTrue($database->inTransaction());

        $database->rollBack(); // 3
        $this->assertTrue($database->inTransaction());

        $database->rollBack(); // 2
        $this->assertTrue($database->inTransaction());

        $database->rollBack(); // 1
        $this->assertFalse($database->inTransaction());

        $database->beginTransaction(); // 1
        $this->assertTrue($database->inTransaction());

        $database->beginTransaction(); // 2
        $this->assertTrue($database->inTransaction());

        $database->beginTransaction(); // 3
        $this->assertTrue($database->inTransaction());

        $database->commit(); // 3
        $this->assertTrue($database->inTransaction());

        $database->commit(); // 2
        $this->assertTrue($database->inTransaction());

        $database->commit(); // 1
        $this->assertFalse($database->inTransaction());

        $database->beginTransaction(); // 1
        $database->beginTransaction(); // 2
        $database->beginTransaction(); // 3
        $this->assertTrue($database->inTransaction());

        $database->commit(); // 3
        $this->assertTrue($database->inTransaction());

        $database->rollBack(); // 2
        $this->assertTrue($database->inTransaction());

        try {
            $database->commit(); // 1
        } catch (Throwable $e) {
            $database->rollBack();
        }
        $this->assertFalse($database->inTransaction());

        $this->expectException(Exception::class);
        $database->rollBack();
    }
}