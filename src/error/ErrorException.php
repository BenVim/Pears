<?php
/*
 * @Author: Ben
 * @Date: 2017-08-18 15:47:00
 * @Last Modified by: Ben
 * @Last Modified time: 2017-08-29 17:32:28
*/

namespace src\error;

use lib\core\BaseObject;
use PHPUnit\Framework\Error\Error;

class ErrorException
{

    const ERROR_NOT_FOUND_USER            = 1; //创建房间失败

    public function getErrorPostData($error_status_code)
    {
        $postData      = array();
        $postData['t'] = 'error';
        $postData['d'] = array('c' => $error_status_code, 'm' => $this->errorMsg($error_status_code));
        return $postData;
    }

    public function getCustomData($error_code, $msg)
    {
        $postData      = array();
        $postData['t'] = 'error';
        $postData['d'] = array('c' => $error_code, 'm' => $msg);
        return $postData;
    }

    public function errorMsg($code)
    {
        $error = [ErrorException::ERROR_NOT_FOUND_USER => "游戏服务器登录失败！",
        ];
        return $error[$code];
    }


}