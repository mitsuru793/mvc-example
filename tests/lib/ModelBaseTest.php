<?php

namespace Tests;

use DateTime;
use DateTimeZone;
use ReflectionProperty;
use ReflectionException;
use PDOException;
use Lib\DatabaseTestCase;
use Lib\ModelBase;

class TestModel extends ModelBase {
    static protected $addTypes = [
        'name' => 'string'
    ];
}
TestModel::setDefaultTableName();
TestModel::setDefaultTypes();

class ModelBaseTest extends DatabaseTestCase
{
    private $model;

    public function setUp()
    {
        parent::setUp();
        $connInfo = array(
            'host'     => 'localhost',
            'dbname'   => $GLOBALS['DB_DBNAME'],
            'dbuser'   => $GLOBALS['DB_USER'],
            'password' => $GLOBALS['DB_PASSWORD']
        );
        ModelBase::setConnectionInfo($connInfo);
        //TestModel = new TestModel();
    }

    public function testSetDefaultName()
    {
        $prop = new ReflectionProperty(new TestModel(), 'tableName');
        $prop->setAccessible(true);
        $tableName = $prop->getValue();
        $this->assertEquals('test_model', $tableName);
    }

    // TODO DateTimeZoneと時刻をDBと合わせる
    public function testQuery()
    {
        $sql = 'SELECT name FROM test_model where id = :id';
        $expected = 'SQLSTATE[HY093]: Invalid parameter number: no parameters were bound';
        try {
            TestModel::query($sql);
            $this->fail('No Exception.');
        } catch (PDOException $e) {
            $this->assertEquals($expected, $e->getMessage());
        }

        $models = TestModel::query($sql, ['id' => 2]);
        $this->assertCount(1, $models);
        $this->assertEquals('name2', $models[0]->name);
        $this->assertSame(['name' => 'name2'], get_object_vars($models[0]));

        $sql = 'SELECT * FROM test_model where id = 1;';
        $model = TestModel::query($sql)[0];
        $expectedDate = (new DateTime('now', new DateTimeZone('Asia/Tokyo')))
                            ->format('Y-m-d H:i:s');
        $this->assertSame(1, $model->id);
        $this->assertSame('name1', $model->name);
        $this->assertSame($expectedDate, $model->createdAt->format('Y-m-d H:i:s'));
        $this->assertSame($expectedDate, $model->updatedAt->format('Y-m-d H:i:s'));
    }

    // TODO DateTimeZoneと時刻をDBと合わせる
    public function testAll()
    {
        $models = TestModel::all();
        $this->assertCount(3, $models);

        $columns = ['id', 'name', 'createdAt', 'updatedAt'];
        for ($i = 0; $i < count($models); $i++) {
            $model = $models[$i];

            $this->assertSame($i+1, $model->id);
            $this->assertSame('name' . ($i+1), $model->name);

            $expeted = (new DateTime('now', new DateTimeZone('Asia/Tokyo')))
                            ->format('Y-m-d H:i:s');
            $actual = $model->createdAt->format('Y-m-d H:i:s');
            $this->assertSame($expeted, $actual);
            $actual = $model->updatedAt->format('Y-m-d H:i:s');
            $this->assertSame($expeted, $actual);
        }
    }

    public function testCreate()
    {
        $createdModel = TestModel::create([
            'name' => 'David'
        ]);
        $selectedModels = TestModel::all();
        $this->assertCount(4, $selectedModels);
        $lastModel = end($selectedModels);

        $this->assertSame(4, $lastModel->id);
        $this->assertSame('David', $lastModel->name);

        $this->assertSame($createdModel->id, $lastModel->id);
        $this->assertSame($createdModel->name, $lastModel->name);
    }

    public function testDelete()
    {
        $this->markTestIncomplete('');
    }
}
