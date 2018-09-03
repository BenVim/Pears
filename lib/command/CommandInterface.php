<?php

namespace lib\core;

interface CommandInterface
{
    /**
     * 激活方法
     * 任何一个命令都必须实现该方法，并拥有一个参数
     *@param array $target 针对目标，可以是一个或多个，自己或他人
     */
    public function activate(array $target); 
}
