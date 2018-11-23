<?php
namespace Test;

require "vendor/autoload.php";

use Minwork\Helper\DateHelper;
use Minwork\Database\Object\Column;
use Minwork\Database\MySql\Database as MySqlDatabase;
use Minwork\Database\MySql\Table as MySqlTable;
use Minwork\Database\Sqlite\Database as SqliteDatabase;
use Minwork\Database\Sqlite\Table as SqliteTable;
use Minwork\Database\Interfaces\TableInterface;
use Minwork\Helper\Formatter;
use Minwork\Database\Object\AbstractTable;
use Minwork\Database\Utility\Condition;
use Minwork\Helper\Random;

class DatabaseTest extends \PHPUnit_Framework_TestCase
{

    const TABLE_NAME = '#!@$%^&*()_+[];./,`~<>:?"\'}{|\\';

    protected $database, $table, $secondPkColumn, $newDataColumn;

    public function testSqlite()
    {
        $this->database = new SqliteDatabase(':memory:');
        $this->table = new SqliteTable($this->database, self::TABLE_NAME, [
            new Column(TableInterface::DEFAULT_PK_FIELD, 'INT', null, false, true),
            new Column('data', 'VARCHAR(255)', 'test'),
            new Column('date', 'DATETIME', null, true)
        ]);
        $this->secondPkColumn = new Column(TableInterface::DEFAULT_PK_FIELD . '2', 'VARCHAR(10)', null, false, true);
        $this->newDataColumn = new Column('data2', 'VARCHAR(511)');
        
        $this->databaseTest();
    }

    public function testMysql()
    {
        global $argv, $argc;
        try {
            switch ($argc) {
                // Database host, name, user and password specified in arguments
                case 6:
                    $this->database = new MySqlDatabase($argv[2], $argv[3], $argv[4], $argv[5]);
                    break;
                // Database host, user and password specified in arguments
                case 5:
                    $this->database = new MySqlDatabase($argv[2], 'test', $argv[3], $argv[4]);
                    break;
                // Database host and name specified in arguments
                case 4:
                    $this->database = new MySqlDatabase($argv[2], $argv[3], 'root', '');
                    break;
                // Database name specified in arguments
                case 3:
                    $this->database = new MySqlDatabase('localhost', $argv[2], 'root', '');
                    break;
                // If no database configuration arguments specified then fallback to default settings
                case 2:
                default:
                    $this->database = new MySqlDatabase('localhost', 'test', 'root', '');
                    break;
            }
        } catch (\PDOException $e) {
            echo "\n\nDatabase test: Cannot connect to MySQL server.\nTry specifing connection parameters via phpunit arguments like:\nvendor/bin/phpunit test/DatabaseTest.php [DBName|DBHost DBName|DBHost DBUser DBPassword|DBHost DBName DBUser DBPassword]\n\n";
            return;
        }
        $this->table = new MySqlTable($this->database, self::TABLE_NAME, [
            new Column(TableInterface::DEFAULT_PK_FIELD, 'INT', null, false, true),
            new Column('data', 'VARCHAR(255)', 'test'),
            new Column('date', 'DATETIME', null, true)
        ]);
        $this->secondPkColumn = new Column(TableInterface::DEFAULT_PK_FIELD . '2', 'VARCHAR(10)', null, false, true);
        $this->newDataColumn = new Column('data2', 'VARCHAR(511)');
        
        $this->databaseTest();
    }

    protected function databaseTest()
    {
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
        
        $this->assertTrue($this->table->create(true));
        
        // Basic check on empty table
        $this->assertEquals(false, $this->table->exists($data));
        $this->assertEquals(0, $this->table->countRows());
        $this->assertEquals(0, $this->table->countRows($data));
        $this->assertEquals(Formatter::removeQuotes(self::TABLE_NAME), $this->table->getName(false));
        $this->assertEquals(TableInterface::DEFAULT_PK_FIELD, $this->table->getPkField());
        
        // Insert
        $this->table->insert($data);
        $this->assertEquals(true, $this->table->exists([
            TableInterface::DEFAULT_PK_FIELD => $data[TableInterface::DEFAULT_PK_FIELD]
        ]));
        $this->assertEquals(1, $this->table->countRows());
        $this->assertEquals(1, $this->table->countRows($data));
        
        $this->assertEquals($data, $this->table->select($data, array_keys($data))
            ->fetch(\PDO::FETCH_ASSOC));
        
        // Check if data column has its default value
        $this->assertEquals([
            'data' => $this->table->getColumns()['data']->getDefaultValue()
        ], $this->table->select([
            TableInterface::DEFAULT_PK_FIELD => $data[TableInterface::DEFAULT_PK_FIELD]
        ], [
            'data'
        ])
            ->fetch(\PDO::FETCH_ASSOC));
        // Check if insert data with default value from schema is same as table data
        $this->assertSame($this->table->format($data, true), $this->table->format($this->table->select([
            TableInterface::DEFAULT_PK_FIELD => $data[TableInterface::DEFAULT_PK_FIELD]
        ], [
            TableInterface::DEFAULT_PK_FIELD,
            'date',
            'data'
        ])
            ->fetch(\PDO::FETCH_ASSOC)));
        
        // Update
        $this->table->update($updateData, [
            TableInterface::DEFAULT_PK_FIELD => $data[TableInterface::DEFAULT_PK_FIELD]
        ]);
        $this->assertEquals($updateData, $this->table->select([
            TableInterface::DEFAULT_PK_FIELD => $updateData[TableInterface::DEFAULT_PK_FIELD]
        ], array_keys($updateData), [
            'date' => -1
        ], 1, TableInterface::DEFAULT_PK_FIELD)
            ->fetch(\PDO::FETCH_ASSOC));
        
        // Insert another row and check condition building
        $this->table->insert($data3);
        
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
        
        $result = $this->table->select($conditions, TableInterface::COLUMNS_ALL, [
            'date' => - 1,
            TableInterface::DEFAULT_PK_FIELD => 1,
            'data' => 'DESC'
        ], 2, TableInterface::DEFAULT_PK_FIELD)->fetchAll(\PDO::FETCH_ASSOC);
        
        $this->assertEquals(2, count($result));
        $this->assertEquals($updateData[TableInterface::DEFAULT_PK_FIELD], $result[0][TableInterface::DEFAULT_PK_FIELD]);
        $this->assertEquals($data3[TableInterface::DEFAULT_PK_FIELD], $result[1][TableInterface::DEFAULT_PK_FIELD]);
        
        // Clean up table
        $this->table->clear();
        $this->assertEquals(0, $this->table->countRows());
        
        // Change table schema
        $columns = $this->table->getColumns();
        $columns = array_merge(array_slice($columns, 0, 1, true), [
            strval($this->secondPkColumn) => $this->secondPkColumn
        ], array_slice($columns, 1, count($columns) - 1, true));
        unset($columns['date']);
        $columns['data'] = $this->newDataColumn;
        $this->table->setColumns($columns);
        $this->assertTrue($this->table->synchronize());
        
        $this->assertEquals(array_keys($data2), $this->table->getColumns(AbstractTable::COLUMNS_FLAG_NAMES));
        $this->assertEquals(0, $this->table->countRows());
        $this->assertEquals([
            TableInterface::DEFAULT_PK_FIELD,
            TableInterface::DEFAULT_PK_FIELD . '2'
        ], $this->table->getPkField());
        
        $this->table->insert($data2);
        
        $currentData = $this->table->select([
            TableInterface::DEFAULT_PK_FIELD => $data2[TableInterface::DEFAULT_PK_FIELD],
            TableInterface::DEFAULT_PK_FIELD . '2' => $data2[TableInterface::DEFAULT_PK_FIELD . '2']
        ])->fetch(\PDO::FETCH_ASSOC);
        
        $this->assertSame($data2, $this->table->format($currentData));
        $this->assertTrue($this->table->remove());
    }
}