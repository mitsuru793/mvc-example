<?php

namespace Lib;

use PDO;
use PDOStatement;
use Lib\Util;

class DBManager {

    private $db;
    private $host;
    private $dbname;
    private $dbuser;
    private $password;
    private $port;

    public function __construct(array $connectInfo)
    {
        $this->host     = $connectInfo['host'];
        $this->dbname   = $connectInfo['dbname'];
        $this->dbuser   = $connectInfo['dbuser'];
        $this->password = $connectInfo['password'];
        $this->port     = $connectInfo['port'];
        $this->initDb();
    }

    public function initDb(): void
    {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;port=%s;',
            $this->host,
            $this->dbname,
            $this->port
        );
        $this->db = new PDO($dsn, $this->dbuser, $this->password);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function select(string $tableName, string $where='', array $whereParams = null): array
    {
        $sql = sprintf('SELECT * FROM %s', $tableName);
        if ($where !== '') {
            $sql .= ' WHERE ' . $where;
        }
        $stmt = $this->getStatement($sql, $whereParams);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return $rows;
    }

    public function delete(string $tableName, string $where='', array $whereParams=null): bool
    {
        $sql = sprintf('DELETE FROM %s', $tableName);
        if ($where !== '') {
            $sql .= ' WHERE ' . $where;
        }
        $stmt = $this->getStatement($sql, $whereParams);
        $isSuccess = $stmt->execute();
        return $isSuccess;
    }

    public function insert(string $tableName, array $data): bool
    {
        $columns = array_keys($data);
        $sql = sprintf('INSERT INTO %s (%s) VALUES (%s)',
            $tableName,
            implode(',', array_map('Lib\Util::toSnakecase', $columns)),
            $this->getValuesPrepare($columns)
        );
        $stmt = $this->getStatement($sql, $data);
        $isSuccess = $stmt->execute();
        return $isSuccess;
    }

    public function update(string $tableName, array $data, string $where='', array $whereParams=null): bool
    {
        $setString = '';
        foreach ($data as $column => $val) {
            $setString .= "{$column} = :set_{$column}, ";
        }
        $setString = rtrim($setString, ', ');

        $sql = sprintf('UPDATE %s SET %s', $tableName, $setString);
        if ($where !== '') {
            $sql .= ' WHERE ' . $where;
        }

        $stmt = $this->getStatement($sql, $whereParams);
        $stmt = $this->getStatement($sql, $data, 'set_', $stmt);
        $isSuccess = $stmt->execute();
        return $isSuccess;
    }

    private function getStatement(string $sql, array $params=null, string $paramsKeyPrefix='', PDOStatement $stmt=null): PDOStatement
    {
        $stmt = $stmt ?? $this->db->prepare($sql);
        if ($params !== null) {
            foreach ($params as $key => $val) {
                $stmt->bindValue(":{$paramsKeyPrefix}{$key}", $val);
            }
        }
        return $stmt;
    }

    private function getValuesPrepare(array $columns): string
    {
        $valuesPrepare = '';
        $columnNum = count($columns);
        for ($i = 0; $i < $columnNum; $i++) {
            $valuesPrepare .= ":{$columns[$i]}";
            if ($i < $columnNum - 1) {
                $valuesPrepare .= ',';
            }
        }
        return $valuesPrepare;
    }
}
