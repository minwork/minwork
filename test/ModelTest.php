<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace Test;

use Exception;
use Minwork\Basic\Model\Model;
use Minwork\Basic\Model\ModelBinder;
use Minwork\Basic\Model\ModelsList;
use Minwork\Database\Interfaces\ColumnInterface;
use Minwork\Database\Interfaces\DatabaseInterface;
use Minwork\Database\Interfaces\TableInterface;
use Minwork\Database\Utility\Query;
use Minwork\Error\Object\Error;
use Minwork\Helper\DateHelper;
use Minwork\Helper\Random;
use Minwork\Operation\Basic\Create;
use Minwork\Operation\Basic\Delete;
use Minwork\Operation\Basic\Update;
use Minwork\Storage\Interfaces\DatabaseStorageInterface;
use Minwork\Validation\Object\Validator;
use Minwork\Validation\Utility\Field;
use Minwork\Validation\Utility\Rule;
use PHPUnit\Framework\TestCase;
use Test\Utils\DatabaseProvider;
use Test\Utils\Timer;

class ModelTest extends TestCase
{
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
     * @param string $table
     * @param string $column
     *
     * @dataProvider databaseProvider
     */
    public function testModelFlow(DatabaseInterface $database, string $table, string $column)
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

        Timer::start("#1 Table creation");
        /** @var $table1 TableInterface|DatabaseStorageInterface */
        $table1 = new $table($database, 'test', [
            new $column('id', ColumnInterface::TYPE_INTEGER, null, false, true, true),
            new $column('name', ColumnInterface::TYPE_STRING),
            new $column('email', ColumnInterface::TYPE_STRING),
            new $column('change_date', ColumnInterface::TYPE_DATETIME)
        ]);
        $table1->create(true);
        Timer::start("#1 Model operations");

        $model = new Model($table1);
        $validator = new class($validatorFunction) extends Validator {
            public function __construct(callable $validatorFunction)
            {
                parent::__construct(
                    new Rule([$this, 'checkContextAndOperation'], new Error('Test error'), Rule::CRITICAL),
                    new Field('name', [
                        new Rule('Minwork\Helper\Validation::isNotEmpty'),
                        new Rule('Minwork\Helper\Validation::isAlphabeticOnly'),
                        //new Rule('Minwork\Helper\Validation::isInt')
                    ]),
                    new Field('email', [
                        new Rule('Minwork\Helper\Validation::isEmail', null, null, true, false),
                        new Rule($validatorFunction, null, null, true,true, false),
                    ])
                );
            }

            public function checkContextAndOperation()
            {
                $context = $this->getContext();
                $operation = $this->getOperation();
                return $context instanceof Model && $operation instanceof Create;
            }
        };
        $this->assertSame($data, $model->setData($data)
            ->getData());
        $this->assertTrue($model->validateThenExecute(new Create(), $validator, $data));
        $this->assertTrue($model->exists());
        $this->assertNotNull($model->getId());
        $this->assertInternalType('int', $model->getId());
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

        // Test getting data on model with set id
        $testModel = new Model(new $table($database, 'test'), $model->getId());
        $this->assertSame('id', $testModel->getStorage()->getPrimaryKey());
        $this->assertSame($data, $testModel->getData());
        $this->assertSame($model->getId(), $testModel->getId());
        $this->assertTrue($testModel->exists());
        unset($testModel);

        
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
        $table1->remove();
        
        // Test multiple columns id
        Timer::start("#2 Table creation");
        /** @var $table1 TableInterface */
        $table1 = new $table($database, 'test', [
            new $column('id_1', ColumnInterface::TYPE_INTEGER, null, false, true),
            new $column('id_2', ColumnInterface::TYPE_STRING, null, false, true),
            new $column('id_3', ColumnInterface::TYPE_BOOLEAN, null, false, true),
            new $column('data', ColumnInterface::TYPE_TEXT)
        ]);
        $table1->create(true);
        Timer::start("#2 Model operations");

        $model = new Model($table1, null, false);
        $data = [
            'id_1' => Random::int(),
            'id_2' => Random::string(255),
            'id_3' => boolval(Random::int(0, 1)),
            'data' => Random::string(2000)
        ];
        
        $this->assertTrue($model->execute(new Create(), $data));
        $this->assertTrue($model->exists());
        $this->assertNotNull($model->getId());
        $this->assertInternalType('int', $model->getId()['id_1']);
        $this->assertInternalType('string', $model->getId()['id_2']);
        $this->assertInternalType('bool', $model->getId()['id_3']);
        $ids = array_diff_key($data, array_flip([
            'data'
        ]));
        $this->assertSame($ids, $model->getNormalizedId());
        $this->assertSame($ids, $model->getId());
        
        $data['data'] = Random::string(2000);
        $this->assertTrue($model->execute(new Update(), $data));
        $this->assertSame($data['data'], $model->getData('data'));
        
        $model = new Model($table1, $ids);
        $this->assertSame(array_keys($ids), $model->getStorage()->getPrimaryKey());
        $this->assertEquals($data['data'], $model->getData('data'));

        Timer::finish();
        unset($model);
        // Clean up table
        $table1->remove();
    }

    /**
     * @param DatabaseInterface $database
     * @param string $table
     * @param string $column
     *
     * @dataProvider databaseProvider
     */
    public function testModelsList(DatabaseInterface $database, string $table, string $column)
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

        Timer::start("#1 Models List - table creation");
        /** @var TableInterface $table1 */
        $table1 = new $table($database, 'test', [
            new $column('id', ColumnInterface::TYPE_INTEGER, null, false, true, true),
            new $column('name', ColumnInterface::TYPE_STRING),
            new $column('key', ColumnInterface::TYPE_INTEGER)
        ]);
        $table1->create(true);

        Timer::start("#1 Models List - operations");

        foreach ($dataList as $data) {
            $table1->insert($data);
        }
        
        $model = new Model($table1);
        
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
        Timer::finish();

        $table1->remove();
    }

    /**
     * @param DatabaseInterface $database
     * @param string $table
     * @param string $column
     * @throws Exception
     *
     * @dataProvider databaseProvider
     */
    public function testModelsBinder(DatabaseInterface $database, string $table, string $column)
    {
        Timer::start("#1 Models binder - table creation");
        /** @var TableInterface $table1 */
        $table1 = new $table($database, 'test', [
            new $column('id', ColumnInterface::TYPE_INTEGER, null, false, true, true),
            new $column('name', ColumnInterface::TYPE_STRING)
        ]);
        $table1->create(true);

        /** @var TableInterface|DatabaseStorageInterface $table2 */
        $table2 = new $table($database, 'test3', [
            new $column('test_id_1', ColumnInterface::TYPE_INTEGER, null, false, true),
            new $column('test_id_2', ColumnInterface::TYPE_INTEGER, null, false, true),
            new $column('data', ColumnInterface::TYPE_STRING)
        ]);
        $table2->create(true);

        Timer::start("#1 Models binder - operations");
        
        $model1 = new Model($table1);
        $model2 = new Model($table1);
        
        $model1->execute(new Create(), [
            'name' => 'Test 1'
        ]);
        $model2->execute(new Create(), [
            'name' => 'Test 2'
        ]);

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

        Timer::finish();

        // Clean up tables
        $table1->remove();
        $table2->remove();
    }
}