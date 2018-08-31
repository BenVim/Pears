<?php
/*
 * @Author: Ben 
 * @Date: 2017-08-10 10:25:06 
 * @Last Modified by: Ben
 * @Last Modified time: 2017-08-10 10:37:29
 * Command Container DI
 */

namespace lib\core;

class CommandContainer
{
    protected $binds;
    protected $instances;

    //bind concrete
    public function bind($abstract, $concrete)
    {
        if ($concrete instanceof Closure) {
            $this->binds[$abstract] = $concrete;
        } else {
            $this->instances[$abstract] = $concrete;
        }
    }

    public function make($abstract, $parameters = [])
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        array_unshift($parameters, $this);
        return call_user_func_array($this->bind[$abstract], $parameters);
    }
}