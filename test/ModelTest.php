<?php
namespace Test;

require "vendor/autoload.php";

use Minwork\Basic\Model\Model;
use Minwork\Validation\Object\Validator;
use Minwork\Validation\Utility\Rule;
use Minwork\Operation\Basic\Create;
use Minwork\Helper\DateHelper;
use Minwork\Operation\Basic\Update;
use Minwork\Operation\Basic\Delete;
use Minwork\Basic\Model\ModelsList;
use Minwork\Database\Utility\Query;
use Minwork\Basic\Model\ModelBinder;
use Minwork\Validation\Utility\Field;
use Minwork\Database\Object\Column;
use Minwork\Helper\Random;
use Minwork\Database\MySql\Database as MySqlDatabase;
use Minwork\Database\Sqlite\Database as SqliteDatabase;

class ModelTest extends \PHPUnit_Framework_TestCase
{

    protected static $database, $table;

    public static function setUpBeforeClass()
    {
        global $argv, $argc;
        try {
            switch ($argc) {
                // Database host, name, user and password specified in arguments
                case 6:
                    self::$database = new MySqlDatabase($argv[2], $argv[3], $argv[4], $argv[5]);
                    break;
                // Database host, user and password specified in arguments
                case 5:
                    self::$database = new MySqlDatabase($argv[2], 'test', $argv[3], $argv[4]);
                    break;
                // Database host and name specified in arguments
                case 4:
                    self::$database = new MySqlDatabase($argv[2], $argv[3], 'root', '');
                    break;
                // Database name specified in arguments
                case 3:
                    self::$database = new MySqlDatabase('localhost', $argv[2], 'root', '');
                    break;
                // If no database configuration arguments specified then fallback to default settings
                case 2:
                default:
                    self::$database = new MySqlDatabase('localhost', 'test', 'root', '');
                    break;
            }
            self::$table = 'Minwork\Database\MySql\Table';
        } catch (\PDOException $e) {
            echo "\n\nModel test: Cannot connect to MySQL server, using SQLite instead.\nTry specifing connection parameters via phpunit arguments like:\nvendor/bin/phpunit test/ModelTest.php [DBName|DBHost DBName|DBHost DBUser DBPassword|DBHost DBName DBUser DBPassword]\n\n";
            // If MySQL is unaccessible connect to SQLite
            self::$database = new SqliteDatabase(':memory:');
            self::$table = 'Minwork\Database\Sqlite\Table';
        }
    }

    public function testModelFlow()
    {
        $validatorFunction = function ($value, $arg1, $arg2) {
            return ! empty($value) && $arg1 && ! $arg2;
        };
        $data = [
            'name' => 'test',
            'email' => 'abc@def.com',
            'change_date' => DateHelper::now()
        ];
        $newData = [
            'name' => 'test2',
            'change_date' => DateHelper::addHours(2, DateHelper::now())
        ];
        $newId = 'unexisting';
        /** @var $table \Minwork\Database\Interfaces\TableInterface */
        $table = new self::$table(self::$database, 'test', [
            new Column('id', 'INT', null, false, true, true),
            new Column('name', 'VARCHAR(255)'),
            new Column('email', 'VARCHAR(255)'),
            new Column('change_date', 'DATETIME')
        ]);
        $table->create(true);
        
        $model = new Model($table);
        $validator = new Validator(
            new Field('name', [
                new Rule('Minwork\Helper\Validation::isNotEmpty'),
                new Rule('Minwork\Helper\Validation::isAlphabeticOnly'),
                //new Rule('Minwork\Helper\Validation::isInt')
            ]),
            new Field('email', [
                new Rule('Minwork\Helper\Validation::isEmail', null, null, true, false),
                new Rule($validatorFunction, null, null, true,true, false)
            ])
        );
        $this->assertSame($data, $model->setData($data)
            ->getData());
        $this->assertTrue($model->validateThenExecute(new Create(), $validator, $data));
        $this->assertTrue($model->exists());
        $this->assertNotNull($model->getId());
        $this->assertEquals([
            'id' => 1
        ], $model->getNormalizedId());
        $this->assertEquals(1, $model->getId());
        $this->assertEquals($data, $model->getData());
        $this->assertEquals($data['name'], $model->getData('name'));
        $this->assertEquals(array_intersect_key($data, array_flip([
            'name',
            'email'
        ])), $model->getData([
            'name',
            'email'
        ]));
        
        $model->execute(new Update(), $newData);
        $this->assertTrue($model->exists());
        $this->assertEquals(array_merge($data, $newData), $model->getData());
        $model->synchronize();
        $id = $model->getId();
        
        $model->setId($newId);
        $this->assertFalse($model->exists());
        
        $model->setId($id);
        $this->assertTrue($model->exists());
        
        $model->execute(new Delete());
        $this->assertFalse($model->exists());
        $this->assertEmpty($model->getData());
        $this->assertNull($model->getId());
        
        // Clean up table
        $table->remove();
        
        // Test multiple columns id
        /** @var $table \Minwork\Database\Interfaces\TableInterface */
        $table = new self::$table(self::$database, 'test', [
            new Column('id_1', 'INT', null, false, true),
            new Column('id_2', 'VARCHAR(255)', null, false, true),
            new Column('id_3', 'BOOLEAN', null, false, true),
            new Column('data', 'TEXT')
        ]);
        $table->create(true);
        
        $model = new Model($table, null, false);
        $data = [
            'id_1' => Random::int(),
            'id_2' => Random::string(255),
            'id_3' => boolval(Random::int(0, 1)),
            'data' => Random::string(2000)
        ];
        
        $this->assertTrue($model->execute(new Create(), $data));
        $this->assertTrue($model->exists());
        $this->assertNotNull($model->getId());
        $ids = array_diff_key($data, array_flip([
            'data'
        ]));
        $this->assertSame($ids, $model->getNormalizedId());
        $this->assertSame($ids, $model->getId());
        
        $data['data'] = Random::string(2000);
        $this->assertTrue($model->execute(new Update(), $data));
        $this->assertSame($data['data'], $model->getData('data'));
        
        $model = new Model($table, $ids);
        $this->assertEquals($data['data'], $model->getData('data'));
        
        // Clean up table
        $table->remove();
    }

    public function testModelsList()
    {
        $dataList = [
            [
                'name' => 'test',
                'key' => 1
            ],
            [
                'name' => 'test2',
                'key' => 1
            ],
            [
                'name' => 'test3',
                'key' => 2
            ]
        ];
        
        $table = new self::$table(self::$database, 'test', [
            new Column('id', 'INT', null, false, true, true),
            new Column('name', 'VARCHAR(255)'),
            new Column('key', 'INT(1)')
        ]);
        $table->create(true);
        
        foreach ($dataList as $data) {
            $table->insert($data);
        }
        
        $model = new Model($table);
        
        $modelsList = new ModelsList($model, new Query([
            'key' => 1
        ]));
        $list = $modelsList->getData(1)->getElements();
        $this->assertSame(1, $modelsList->getPage());
        $this->assertNull($modelsList->getOnPage());
        $this->assertEquals(2, $modelsList->getTotal());
        $this->assertEquals(2, count($list));
        $this->assertEquals('1', $list[0]->getId());
        $this->assertEquals('test2', $list[1]->getData('name'));
        $this->assertEquals($dataList[0], $list[0]->getData());
        $list = $modelsList->reset()
            ->setQuery(new Query())
            ->getData(1, 2)
            ->getElements();
        $this->assertEquals(1, $modelsList->getPage());
        $this->assertEquals(2, $modelsList->getOnPage());
        $this->assertEquals(3, $modelsList->getTotal());
        $this->assertEquals(2, count($list));

        // Model list page edge case
        $list = $modelsList->reset()
            ->setQuery(new Query())
            ->getData(1000000, 2)
            ->getElements();
        $this->assertSame(2, $modelsList->getPage());
        $this->assertEquals(2, $modelsList->getOnPage());
        $this->assertEquals(3, $modelsList->getTotal());
        $this->assertEquals(1, count($list));

        $list = $modelsList->reset()
            ->setQuery(new Query())
            ->getData(-1000000, 100000000)
            ->getElements();
        $this->assertSame(1, $modelsList->getPage());
        $this->assertEquals(100000000, $modelsList->getOnPage());
        $this->assertEquals(3, $modelsList->getTotal());
        $this->assertEquals(3, count($list));

        $list = $modelsList->reset()
        ->setQuery(new Query())
        ->getData(0, -100000000)
        ->getElements();
        $this->assertSame(1, $modelsList->getPage());
        $this->assertEquals(1, $modelsList->getOnPage());
        $this->assertEquals(3, $modelsList->getTotal());
        $this->assertEquals(1, count($list));

        $list = $modelsList->reset()
            ->setQuery(new Query(['key' => 3]))
            ->getData(1, 50)
            ->getElements();
        $this->assertSame(1, $modelsList->getPage());
        $this->assertEquals(50, $modelsList->getOnPage());
        $this->assertEquals(0, $modelsList->getTotal());
        $this->assertEquals(0, count($list));
        
        // Clean up
        foreach ($list as $model) {
            $model->synchronize();
        }
        $table->remove();
    }

    public function testModelsBinder()
    {
        $table = new self::$table(self::$database, 'test', [
            new Column('id', 'INT', null, false, true, true),
            new Column('name', 'VARCHAR(255)')
        ]);
        $table->create(true);
        
        $model1 = new Model($table);
        $model2 = new Model($table);
        
        $model1->execute(new Create(), [
            'name' => 'Test 1'
        ]);
        $model2->execute(new Create(), [
            'name' => 'Test 2'
        ]);
        
        $table2 = new self::$table(self::$database, 'test3', [
            new Column('test_id_1', 'INT', null, false, true),
            new Column('test_id_2', 'INT', null, false, true),
            new Column('data', 'VARCHAR(255)')
        ]);
        $table2->create(true);
        
        $data = [
            'data' => 'Test 3'
        ];
        $newData = [
            'data' => 'Test 4'
        ];
        $modelBinder = new ModelBinder($table2, [
            $model1,
            $model2
        ]);
        
        $this->assertTrue($modelBinder->execute(new Create(), $data));
        $this->assertTrue($modelBinder->exists());
        $this->assertEquals([
            $model1->getBindingFieldName() . '_1' => $model1->getId(),
            $model2->getBindingFieldName() . '_2' => $model2->getId()
        ], $modelBinder->getId());
        $this->assertEquals($data, $modelBinder->getData());
        $modelBinder->execute(new Update(), $newData);
        $this->assertTrue($modelBinder->exists());
        $this->assertEquals(array_merge($data, $newData), $modelBinder->getData());
        $modelBinder->execute(new Delete());
        $this->assertFalse($modelBinder->exists());
        $this->assertEmpty($modelBinder->getData());
        $this->assertNull($modelBinder->getId());
        
        // Clean up tables
        $table->remove();
        $table2->remove();
    }
}