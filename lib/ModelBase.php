<?php

namespace Lib;

use PDO;
use PDOStatement;
use Lib\Util;
use Lib\Cast;

class ModelBase
{
    private static $connectInfo;
    protected static $db;
    protected static $tableName;
    protected static $nextId;
    protected static $types;
    protected static $commonTypes = [
        'id' => 'int',
        'createdAt' => 'date',
        'updatedAt' => 'date'
    ];
    protected static $addTypes = [];

    public function __construct()
    {
        $this->initDb();

        if (self::$tableName === null) {
            $this->setDefaultTableName();
        }
    }

    static public function init(): void
    {
        self::setDefaultTypes();
        self::setDefaultTableName();
        self::$nextId = self::setNextId();
        self::initDb();
    }

    static public function setConnectInfo(array $connectInfo): void
    {
        self::$connectInfo = $connectInfo;
    }

    static public function query(string $sql, array $params = null): array
    {
        $stmt = self::getPrepareSql($sql, $params);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $models = [];
        foreach ($rows as $row) {
            $className = get_called_class();
            $model = new $className();
            foreach ($row as $key => $val) {
                $prop = Util::toCamelcase($key);
                $model->$prop = Cast::to(self::$types[$prop], $val);
            }
            $models[] = $model;
        }
        return $models;
    }

    static public function all(): array
    {
        $sql = sprintf('SELECT * FROM %s', self::$tableName);
        $models = self::query($sql);
        return $models;
    }

    static public function create(array $data): self
    {
        $dateStr = date('Y-m-d H:i:s');
        $data['createdAt'] = $dateStr;
        $data['updatedAt'] = $dateStr;
        $data['id'] = self::getNewId();

        $model = new self();
        $columns = [];
        $values = [];
        foreach ($data as $column => $val) {
            $columns[] = $column;
            $model->$column = $val;
        }

        $valuesPrepare = '';
        $columnNum = count($columns);
        for ($i = 0; $i < $columnNum; $i++) {
            $valuesPrepare .= ":{$columns[$i]}";
            if ($i < $columnNum - 1) {
                $valuesPrepare .= ',';
            }
        }

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            self::$tableName,
            implode(',', array_map('Lib\Util::toSnakecase', $columns)),
            $valuesPrepare
        );
        $stmt = self::$db->prepare($sql);
        if ($data !== null) {
            foreach ($data as $column => $val) {
                $stmt->bindValue(":{$column}", $val);
            }
        }
        $is_success = $stmt->execute();
        return $model;
    }

    public function delete(string $where, array $params=null): bool
    {
        $sql = sprintf('DELETE FROM %s', $this->name);
        if ($where !== '') {
            $sql .= 'WHERE' . $where;
        }
        $stmt = self::$db->prepare($sql);
        if ($params !== null) {
            foreach ($params as $key => $val) {
                $stmt->bindValue(":{$key}", $val);
            }
        }
        $result = $stmt->execute();

        return $result;
    }

    static public function count(): int
    {
        $sql = sprintf('SELECT COUNT(*) FROM %s', self::$tableName);
        $result = self::$db->prepare($sql);
        $result->execute();
        $numberOfRows = $result->fetchColumn();
        return (int)$numberOfRows;
    }

    static function setDefaultTableName(): void
    {
        $className = explode('\\', get_called_class());
        $className = array_pop($className);
        $len = strlen($className);
        $tableName = '';
        for ($i = 0; $i < $len; $i++) {
            $char = substr($className, $i, 1);
            $lower = strtolower($char);
            if ($i > 0 && $char !== $lower) {
                $tableName .= '_';
            }
            $tableName .= $lower;
        }
        self::$tableName = $tableName;
    }

    static function setConnectionInfo($connectInfo): void
    {
        self::$connectInfo = $connectInfo;
    }

    static function setDefaultTypes(): void
    {
        foreach (static::$commonTypes as $key => $val) {
            static::$types[$key] = $val;
        }
        foreach (static::$addTypes as $key => $val) {
            static::$types[$key] = $val;
        }
    }

    static protected function initDb(): void
    {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;port=3306;',
            self::$connectInfo['host'],
            self::$connectInfo['dbname']
        );
        self::$db = new PDO($dsn, self::$connectInfo['dbuser'], self::$connectInfo['password']);
        self::$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    static protected function setNextId(): int
    {
        $sql = sprintf('SELECT MAX(id) + 1 FROM %s', self::$tableName);
        $result = self::$db->prepare($sql);
        $result->execute();
        $numberOfRows = $result->fetchColumn();
        return (int)$numberOfRows;
    }

    static protected function getPrepareSql(string $sql, array $params = null): PDOStatement
    {
        $stmt = self::$db->prepare($sql);
        if ($params !== null) {
            foreach ($params as $key => $val) {
                $stmt->bindValue(":{$key}", $val);
            }
        }
        return $stmt;
    }

    static private function getNewId(): int
    {
        $sql = sprintf('SELECT id FROM %s ORDER BY id DESC', self::$tableName);
        $stmt = self::$db->prepare($sql);
        $stmt->execute();
        $newId = $stmt->fetch()['id'] + 1;
        return $newId;
    }
}
