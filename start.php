<?php
/**
 * Created by PhpStorm.
 * User: jerry
 * Date: 2018/9/4
 * Time: 10:36 PM
 */

define("INFO_LEVEL", 1);

date_default_timezone_set("Asia/Shanghai");

define("WORKING_DIR", __DIR__ . "/");

require(WORKING_DIR . "src/translate/Console.php");
require(WORKING_DIR . "src/translate/Server.php");

$host = "0.0.0.0";
$port = 9502;

use translate\Server;
use translate\Console;

$server = new Server($host, $port);
$server->start();

function loadAllClass($dir) {
    $dir_obj = scandir($dir);
    unset($dir_obj[0], $dir_obj[1]);
    foreach ($dir_obj as $m) {
        $taskFileName = explode(".", $m);
        if (is_dir($dir . $m . "/")) loadAllClass($dir . $m . "/");
        else {
            if (count($taskFileName) < 2 || $taskFileName[1] != "php") continue;
            Console::info("Loading " . $m);
            require_once($dir . $m);
        }
    }
}