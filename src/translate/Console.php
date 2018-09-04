<?php
/**
 * Created by PhpStorm.
 * User: jerry
 * Date: 2018/9/4
 * Time: 10:37 PM
 */

namespace translate;


class Console
{
    static function setColor($string, $color = "") {
        switch ($color) {
            case "red":
                return "\x1b[38;5;203m" . $string . "\x1b[m";
            case "green":
                return "\x1b[38;5;83m" . $string . "\x1b[m";
            case "yellow":
                return "\x1b[38;5;227m" . $string . "\x1b[m";
            case "blue":
                return "\033[34m" . $string . "\033[0m";
            case "lightpurple":
                return "\x1b[38;5;207m" . $string . "\x1b[m";
            case "lightblue":
                return "\x1b[38;5;87m" . $string . "\x1b[m";
            case "gold":
                return "\x1b[38;5;214m" . $string . "\x1b[m";
            case "gray":
                return "\x1b[38;5;59m" . $string . "\x1b[m";
            case "pink":
                return "\x1b[38;5;207m" . $string . "\x1b[m";
            case "lightlightblue":
                return "\x1b[38;5;63m" . $string . "\x1b[m";
            default:
                return $string;
        }
    }

    static function trace($obj, $head = null) {
        if ($head === null) $head = "[TRACE] " . date("H:i:s") . " ";
        if (INFO_LEVEL < 1) return;
        if (!is_string($obj)) var_dump($obj);
        else echo($head . $obj . "\n");
    }

    static function debug($obj, $head = null) {
        if ($head === null) $head = "[DEBUG] " . date("H:i:s") . " ";
        if (INFO_LEVEL < 2) return;
        if (!is_string($obj)) var_dump($obj);
        else echo(self::setColor($head . $obj, "green") . "\n");
    }

    static function error($obj, $head = null) {
        if ($head === null) $head = "[ERROR] " . date("H:i:s") . " ";
        if (!is_string($obj)) var_dump($obj);
        else echo(self::setColor($head . $obj, "red") . "\n");
    }

    static function ctrl($obj, $head = null) {
        if ($head === null) $head = date("H:i:s") . " [WEB CTRL] ";
        if (!is_string($obj)) var_dump($obj);
        else echo(self::setColor($head . $obj, "yellow") . "\n");
    }

    static function info($obj, $head = null) {
        if ($head === null) $head = date("H:i:s ") . "[INFO] ";
        if (!is_string($obj)) var_dump($obj);
        else echo(self::setColor($head . $obj, "blue") . "\n");
    }

    static function put($obj, $color = "") {
        if (!is_string($obj)) var_dump($obj);
        else echo(self::setColor($obj, $color) . "\n");
    }
}