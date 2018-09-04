<?php
/**
 * Created by PhpStorm.
 * User: jerry
 * Date: 2018/9/4
 * Time: 10:39 PM
 */

namespace translate;

use translate\event\HTTPEvent;
use swoole_http_request, swoole_http_response, swoole_server;

class Server
{
    public static $obj = null;
    public $host;
    public $port;

    public $server;

    public function __construct($host, $port) {
        if (self::$obj !== null) die("Cannot run two Framework in one process.\n");
        $this->host = $host;
        $this->port = $port;
        $this->selfCheck();
        $this->server = new \swoole_http_server($host, $port);
        Console::info("Server starting...");
        $this->server->set([
            "worker_num" => 1,
            "dispatch_mode" => 2
        ]);
        $this->server->on("WorkerStart", [$this, "onWorkerStart"]);
        $this->server->on('request', [$this, "onRequest"]);
        @mkdir(WORKING_DIR . "temp/", 0777, true);
    }

    public function start() {
        $this->server->start();
    }

    /**
     * 框架启动后的Worker运行第一个函数
     * @param swoole_server $server
     * @param $worker_id
     */
    public function onWorkerStart(swoole_server $server, $worker_id) {
        loadAllClass(WORKING_DIR . "src/");
    }

    /******************* HTTP 响应 ******************
     * @param swoole_http_request $request
     * @param swoole_http_response $response
     */
    public function onRequest($request, $response) { new HTTPEvent($request, $response); }

    private function selfCheck() {
        if (!extension_loaded("swoole")) die("Can not find swoole extension.\n");
        if (!function_exists("mb_substr")) die("Can not find mbstring extension.\n");
        if (substr(PHP_VERSION, 0, 1) != "7") die("PHP >=7 required.\n");
    }
}