<?php
/**
 * Created by PhpStorm.
 * User: ben
 * Date: 2018/8/31
 * Time: 17:42
 */

namespace src\protocol;


/**
 * Trait ErrorProtocol 错误的协议组装器
 * @package src\protocol
 *
 */
trait ErrorProtocol
{

    public function getErrorProtocol(){

    }

    /**
     * @param $error_status_code
     * @return array 根据code值返回消息格式
     */
    public function getErrorPostData($error_status_code)
    {
        $postData      = array();
        $postData['t'] = 'error';
        $postData['d'] = array('c' => $error_status_code, 'm' => ErrorException::errorMsg($error_status_code));
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

}