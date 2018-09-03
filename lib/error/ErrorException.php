<?php
/*
 * @Author: Ben
 * @Date: 2017-08-18 15:47:00
 * @Last Modified by: Ben
 * @Last Modified time: 2017-08-29 17:32:28
*/

namespace src\error;

use lib\core\BaseObject;

class ErrorException
{

    const ERROR_NOT_FOUND_USER = 1001; //创建房间失败

    /**
     * @param $error_status_code
     * @return array 根据code值返回消息格式
     */
    public function getErrorPostData($error_status_code)
    {
        $postData      = array();
        $postData['t'] = 'error';
        $postData['d'] = array('c' => $error_status_code, 'm' => $this->errorMsg($error_status_code));
        return $postData;
    }

    /**
     * @param       $error_code
     * @param       $msg
     * @param array $data
     * @return array 返回消息
     */
    public function getCustomData($error_code, $msg, $data=[])
    {
        $postData      = array();
        $postData['t'] = 'error';
        $postData['d'] = array('c' => $error_code, 'm' => $msg, 'd'=>$data);
        return $postData;
    }

    public static function errorMsg($code)
    {
        $error = [ErrorException::ERROR_NOT_FOUND_USER => "游戏服务器登录失败！",
        ];
        return $error[$code];
    }


}