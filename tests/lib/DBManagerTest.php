<?php

namespace Tests;

use PDOException;
use Lib\DatabaseTestCase;
use Lib\DBManager;

class ModelBaseTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->db = new DBManager([
            'host'     => 'localhost',
            'dbname'   => $GLOBALS['DB_DBNAME'],
            'dbuser'   => $GLOBALS['DB_USER'],
            'password' => $GLOBALS['DB_PASSWORD'],
            'port'     => $GLOBALS['DB_PORT']
        ]);
        $this->tableName = 'test_model';
    }

    public function testSelect()
    {
        $rows = $this->db->select($this->tableName);
        $this->assertCount(3, $rows);
        for ($i = 0; $i < count($rows); $i++) {
            $n = $i + 1;
            $this->assertSame("{$n}", $rows[$i]['id']);
            $this->assertSame("name{$n}", $rows[$i]['name']);
        }

        $where = 'id = :id AND name = :name';
        $whereParams = ['id' => 2, 'name' => 'name2'];
        $rows = $this->db->select($this->tableName, $where, $whereParams);

        $this->assertCount(1, $rows);

        $row = $rows[0];
        $this->assertSame("2", $row['id']);
        $this->assertSame("name2", $row['name']);
    }

    public function testDelete()
    {
        $where = 'id = :id AND name = :name';
        $whereParams = ['id' => 3, 'name' => 'name3'];
        $isSuccess = $this->db->delete($this->tableName, $where, $whereParams);

        $rows = $this->db->select($this->tableName);
        $this->assertCount(2, $rows);
        $this->assertNotSame(3, end($rows)['id']);
        $this->assertTrue($isSuccess);

        $isSuccess = $this->db->delete($this->tableName, $where, $whereParams);
        $rows = $this->db->select($this->tableName);
        $this->assertCount(2, $rows);
        $this->assertTrue($isSuccess); // TODO
    }

    public function testInsert()
    {
        $data = ['name' => 'name4'];
        $isSuccess = $this->db->insert($this->tableName, $data);
        $this->assertTrue($isSuccess);

        $rows = $this->db->select($this->tableName);
        $this->assertCount(4, $rows);
        $this->assertSame('4', end($rows)['id']);
        $this->assertSame('name4', end($rows)['name']);
    }

    public function testUpdate()
    {
        $data = ['name' => 'chenged'];
        $isSuccess = $this->db->update($this->tableName, $data);
        $this->assertTrue($isSuccess);

        $rows = $this->db->select($this->tableName);
        $this->assertCount(3, $rows);
        foreach ($rows as $row) {
            $this->assertSame('chenged', $row['name']);
        }

        $data = ['name' => 'back'];
        $where = 'id = :id';
        $whereParams = ['id' => 3];
        $isSuccess = $this->db->update($this->tableName, $data, $where, $whereParams);
        $rows = $this->db->select($this->tableName);
        $lastRow = end($rows);
        $this->assertSame('3', $lastRow['id']);
        $this->assertSame('back', $lastRow['name']);
    }
}
