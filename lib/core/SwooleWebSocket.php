<?php

namespace lib\core;

use swoole_websocket_server;
use lib\core\Config;
use lib\core\Log;

class SwooleWebSocket
{
    private $server;
    protected $callbacks = [];

    function __construct($host, $port, $obj)
    {

        $serverSetting = array(
            'worker_num'               => Config::get('server.worker_num'),
            'max_request'              => Config::get('server.max_request'),
            'dispatch_mode'            => Config::get('server.dispatch_mode'),
            'debug_mode'               => Config::get('server.debug_mode'),
            'task_worker_num'          => Config::get('server.task_worker_num'),
            'task_ipc_mode'            => Config::get('server.task_ipc_mode'),
            'daemonize'                => Config::get('server.daemonize'),
            'heartbeat_check_interval' => Config::get('server.heartbeat_check_interval'),
            'log_file'                 => getcwd() . '/Runtime/swoole.log',
        );

        $isHttps = Config::get('server.https');
        if ($isHttps) {
            $serverSetting['ssl_key_file']  = Config::get('server.ssl_key_file');
            $serverSetting['ssl_cert_file'] = Config::get('server.ssl_cert_file');
            $this->server                   = new swoole_websocket_server($host, $port, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
        } else {
            $this->server = new swoole_websocket_server($host, $port);
        }

        $this->server->set($serverSetting);

        $this->server->on('open', [$obj, 'onOpen']);
        $this->server->on('Message', [$obj, 'onMessage']);
        $this->server->on('Close', [$obj, 'onClose']);
        $this->server->on('task', [$obj, 'onTask']);
        $this->server->on('finish', [$obj, 'onFinish']);
        $this->server->on('Start', [$obj, 'onStart']);
        $this->server->on('WorkerStart', [$obj, 'onWorkerStart']);
    }

    public function start()
    {
        return $this->server->start();
    }

}

?>
