<?php

namespace Lib;

abstract class DatabaseTestCase extends \PHPUnit_Extensions_Database_TestCase
{
    static public $pdo = null;
    public $connection = null;

    public function getConnection()
    {
        if ($this->connection === null) {
            if (self::$pdo === null) {
                self::$pdo = new \PDO(
                    $GLOBALS['DB_DSN'],
                    $GLOBALS['DB_USER'],
                    $GLOBALS['DB_PASSWORD']
                );
            }
            $this->connection = $this->createDefaultDBConnection(self::$pdo, $GLOBALS['DB_DBNAME']);
        }
        return $this->connection;
    }

    public function getDataSet()
    {
        $compositeDs = new \PHPUnit_Extensions_Database_DataSet_CompositeDataSet();

        $dir = dirname(__FILE__) . '/../tests/fixture';
        $fh = opendir($dir);
        while ($file = readdir($fh)) {
            if (preg_match('/^\./', $file)) {
                continue;
            }
            if (preg_match('/\.yml$/', $file)) {
                $ds = new \PHPUnit_Extensions_Database_DataSet_YamlDataSet("$dir/$file");
                $compositeDs->addDataSet($ds);
            }
        }

        return $compositeDs;
    }
}
