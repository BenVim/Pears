<?php

namespace lib\core;

use PDO;
use Exception;
use lib\core\Log;
use lib\core\Config;
use src\util\RedisUtility;

class PDORepository
{
    const PDO_ALL         = '0';
    const PDO_ROW         = '1';
    const PDO_MODEL_QUERY = 1;
    const PDO_MODEL_EXEC  = 2;

    protected static $_instance = null;
    protected $dbName = '';
    protected $dsn;
    protected $dbh;

    private function __construct()
    {
        $this->connectDb();
    }

    private function __clone()
    {

    }

    private function connectDb()
    {
        $dbHost    = Config::get('db.default.host');
        $dbUser    = Config::get('db.default.username');
        $dbPasswd  = Config::get('db.default.password');
        $dbName    = Config::get('db.default.dbname');
        $dbCharset = 'utf8';

        try {
            $this->dsn = 'mysql:host=' . $dbHost . ';dbname=' . $dbName;
            $this->dbh = new PDO($this->dsn, $dbUser, $dbPasswd, [PDO::ATTR_PERSISTENT => true]);
            $this->dbh->exec('SET character_set_connection=' . $dbCharset . ', character_set_results=' . $dbCharset . ', character_set_client=binary');
            return true;
        } catch (Exception $e) {
            $this->outputError($e->getMessage());
            return false;
        }
    }

    public static function getInstance()
    {


        if (self::$_instance === null) {
            dump('new self');
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function query($strSql, $queryMode = PDORepository::PDO_ALL)
    {
        $result = $this->querySQL($strSql, PDORepository::PDO_MODEL_QUERY, $queryMode);
        return $result;
    }

    public function update($table, $arrayDataValue, $where = '')
    {
        $this->checkFields($table, $arrayDataValue);
        if ($where) {
            $strSql = '';
            foreach ($arrayDataValue as $key => $value) {
                $strSql .= ", `$key`='$value'";
            }
            $strSql = substr($strSql, 1);
            $strSql = "UPDATE `$table` SET $strSql WHERE $where";
        } else {
            $strSql = "REPLACE INTO `$table` (`" . implode('`,`', array_keys($arrayDataValue)) . "`) VALUES ('" . implode("','", $arrayDataValue) . "')";
        }
        //$result = $this->dbh->exec($strSql);
        $result = $this->querySQL($strSql, PDORepository::PDO_MODEL_EXEC);
        return $result;
    }


    public function insert($table, $arrayDataValue)
    {
        $in_id = false;
        $this->checkFields($table, $arrayDataValue);
        $strSql = "INSERT INTO `$table` (`" . implode('`,`', array_keys($arrayDataValue)) . "`) VALUES ('" . implode("','", $arrayDataValue) . "')";
        //$result = $this->dbh->exec($strSql);
        $result = $this->querySQL($strSql, PDORepository::PDO_MODEL_EXEC);
        if($result){
            dump('xxxxx', $result);
            $in_id = $this->dbh->lastInsertId();
        }
        return $in_id;
    }

    public function delete($table, $where = '')
    {
        if ($where != '') {
            $strSql = "DELETE FROM `$table` WHERE $where";
            $result = $this->querySQL($strSql, PDORepository::PDO_MODEL_EXEC);
            if(!empty($result)){
                return $result;
            }
        }
        return false;
    }


    public function beginTransaction()
    {
        $this->dbh->beginTransaction();
    }

    public function commit()
    {
        $this->dbh->commit();
    }

    public function rollback()
    {
        $this->dbh->rollback();
    }

    private function checkFields($table, $arrayFields)
    {
        $fields = $this->getFields($table);
        foreach ($arrayFields as $key => $value) {
            if (!in_array($key, $fields)) {
                $this->outputError("Unknown column `$key` in field list.");
            }
        }
    }

    public function getFields($table)
    {
        $fields = [];
        //$recordset = $this->dbh->query("SHOW COLUMNS FROM $table");
        $result = $this->querySQL("SHOW COLUMNS FROM $table", PDORepository::PDO_MODEL_QUERY, PDORepository::PDO_ALL);
        foreach ($result as $rows) {
            $fields[] = $rows['Field'];
        }
        return $fields;
    }

    private function outputError($strErrMsg)
    {
        throw new Exception('MySQL Error: ' . $strErrMsg);
    }



    private function querySQL($sql, $model = 1, $queryMode = PDORepository::PDO_ALL)
    {
        $res    = false;
        $result = [];
        for ($i = 0; $i < 2; $i++) {
            if ($model == PDORepository::PDO_MODEL_QUERY) {
                $recordSet = $this->dbh->query($sql);
                if($recordSet){
                    $recordSet->setFetchMode(PDO::FETCH_ASSOC);
                    if ($queryMode == PDORepository::PDO_ALL) {
                        $result = $recordSet->fetchAll();
                    } elseif ($queryMode == PDORepository::PDO_ROW) {
                        $result = $recordSet->fetch();
                    }
                }
            } else {
                $recordSet = $this->dbh->exec($sql);
                $result = $recordSet;
            }

            if ($recordSet === false) {
                if ($this->dbh->errorCode() == 2006 or $this->dbh->errorCode() == 2013 or $this->dbh->errorCode() == 0) {
                    $connectionResult = $this->checkConnection();
                    dump("connect:");
                    if ($connectionResult === true) {
                        dump("connect:true");
                        continue;
                    }
                }
                return false;
            }
            break;
        }
        if (!$result) {
            return false;
        }
        return $result;
    }

    protected function checkConnection()
    {
        $this->destruct();
        dump("checkConnection......................");
        return $this->connectDb();
    }

    public function destruct()
    {
        $this->dbh                = null;
        PDORepository::$_instance = null;
    }


}

