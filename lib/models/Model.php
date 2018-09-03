<?php

namespace lib\core;

/*
 * @Author: Ben 
 * @Date: 2017-08-09 15:21:16 
 * @Last Modified by: Ben
 * @Last Modified time: 2017-08-14 14:25:19
 */
class Model extends BaseObject
{


    protected $_table;//tablename

    protected $db;

    function __construct($conn)
    {
        $this->db = $conn;//PDORepository::getInstance();
    }

    protected function getTableName()
    {
        if (!isset($_table)) {
            $name = substr(get_class($this), 0, -strlen(C('DEFAULT_M_LAYER')));
            return $name;
        } else {
            return $_table;
        }
    }

    protected function updateData($table, $data, $map)
    {
        $where = $this->getArrayToWhere($map);
        return $this->db->update($table, $data, $where);
    }

    protected function destruct(){
        $this->db->destruct();
    }

    public function getArrayToWhere($map)
    {

        if ($map) {
            $where = [];
            foreach ($map as $key => $value) {
                if (is_string($value)) {
                    $value = "'$value'";
                }
                $where[] = $key . "=" . $value;
            }
        } else {
            return '';
        }
        return implode(' and ', $where);
    }


}