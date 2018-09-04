<?php
/**
 * Created by PhpStorm.
 * User: jerry
 * Date: 2018/9/4
 * Time: 10:46 PM
 */

namespace translate\event;

use swoole_http_request, swoole_http_response;

class HTTPEvent
{
    const LANG = [
        "*" => "en",
        "zh" => "zh",
        "zh-CN" => "zh",
        "zhtw" => "zh-TW",
        "en" => "en",
        "ar" => "ar",
        "eo" => "eo",
        "fr" => "fr",
        "de" => "de",
        "haw" => "haw",
        "it" => "it",
        "ja" => "ja",
        "ko" => "ko",
        "pl" => "pl",
        "pt" => "pt",
        "ru" => "ru",
        "es" => "es",
        "sv" => "sv",
        "th" => "th",
        "tr" => "tr",
        "uk" => "uk",
        "vi" => "vi",
        "ms" => "ms",
        "la" => "la",
        "jv" => "jv",
        "bg" => "bg",
        "da" => "da"
    ];

    public function __construct(swoole_http_request $request, swoole_http_response $response) {
        if ($request->server["request_method"] != "POST") {
            if (!isset($request->get["file_type"])) {
                $response->header("Content-Type", 'application/json; charset=utf-8');
                $response->end(json_encode(["status" => 199, "err_message" => "Invalid parameter."]));
                return;
            }
            switch ($request->get["file_type"]) {
                case "record":
                    if (!isset($request->get["record_id"])) {
                        $response->header("Content-Type", 'application/json; charset=utf-8');
                        $response->end(json_encode(["status" => 199, "err_message" => "Invalid parameter."]));
                        return;
                    }
                    if (!$this->isMd5($request->get["record_id"])) {
                        $response->header("Content-Type", 'application/json; charset=utf-8');
                        $response->end(json_encode(["status" => 199, "err_message" => "Invalid md5."]));
                        return;
                    }
                    if (!file_exists(WORKING_DIR . "temp/" . $request->get["record_id"] . ".ts")) {
                        $response->header("Content-Type", 'application/json; charset=utf-8');
                        $response->end(json_encode(["status" => 199, "err_message" => "Invalid filename."]));
                        return;
                    }
                    $file = file_get_contents(WORKING_DIR . "temp/" . $request->get["record_id"] . ".ts");
                    $response->header("Content-Type", "application/x-linguist");
                    $response->end($file);
                    break;
            }
            return;
        }
        $dat = $request->rawContent();
        $dat = json_decode($dat, true);
        if ($dat === null) {
            $response->end(json_encode(["status" => 199, "err_message" => "Invalid parameter."]));
            return;
        }
        if (!isset($dat["trans-type"])) {
            $response->end(json_encode(["status" => 199, "err_message" => "Invalid parameter. \"trans-type\" required."]));
            return;
        }
        switch ($dat["trans-type"]) {
            case "translate":
                if (!isset($dat["origin"]) || !isset($dat["target"])) {
                    $response->end(json_encode(["status" => 199, "err_message" => "Invalid parameter. \"origin\" and \"target\" required."]));
                    return;
                }
                if (!isset($dat["text"])) {
                    $response->end(json_encode(["status" => 199, "err_message" => "Invalid parameter. \"text\" required."]));
                    return;
                }
                $result = $this->translate($dat["text"], $dat["origin"], $dat["target"], false, false);
                $response->header("Content-Type", 'application/json; charset=utf-8');
                $response->end(json_encode($result));
                break;
            case "translate-with-voice":
                if (!isset($dat["origin"]) || !isset($dat["target"])) {
                    $response->end(json_encode(["status" => 199, "err_message" => "Invalid parameter. \"origin\" and \"target\" required."]));
                    return;
                }
                if (!isset($dat["text"])) {
                    $response->end(json_encode(["status" => 199, "err_message" => "Invalid parameter. \"text\" required."]));
                    return;
                }
                $result = $this->translate($dat["text"], $dat["origin"], $dat["target"], true, false);
                $response->header("Content-Type", 'application/json; charset=utf-8');
                $response->end(json_encode($result));
                break;
            case "voice":
                if (!isset($dat["origin"]) || !isset($dat["target"])) {
                    $response->end(json_encode(["status" => 199, "err_message" => "Invalid parameter. \"origin\" and \"target\" required."]));
                    return;
                }
                if (!isset($dat["text"])) {
                    $response->end(json_encode(["status" => 199, "err_message" => "Invalid parameter. \"text\" required."]));
                    return;
                }
                $result = $this->translate($dat["text"], $dat["origin"], $dat["target"], true, true);
                $response->header("Content-Type", 'application/json; charset=utf-8');
                $response->end(json_encode($result));
                break;
        }
    }

    /**
     * @param $text
     * @param $origin
     * @param $target
     * @param bool $need_voice
     * @param bool $no_translate
     * @return array
     */
    public function translate($text, $origin, $target, $need_voice = false, $no_translate = false) {
        $rand = mt_rand(10000, 99999);
        $filename = WORKING_DIR . "temp/" . $rand . ".txt";
        file_put_contents($filename, $text);
        if (!array_key_exists($origin, self::LANG) || !array_key_exists($origin, self::LANG)) {
            return ["status" => 198, "err_message" => "invalid translate language."];
        }
        if ($origin == "*")
            $origin = exec('cat ' . $filename . ' | trans -id | grep Code | awk -F " " \'{print $2}\' | sed -r "s/\x1B\[([0-9]{1,2}(;[0-9]{1,2})?)?[m|K]//g"');
        if (($origin == "zh-CN" || $origin == "zh") && $target == "*") $target = "en";
        elseif ($origin == "en" && $target == "*") $target = "zh";
        if ($origin == "*") $origin = "";
        if ($target == "*") $target = "";
        if ($need_voice === true && $no_translate === false) {
            $record_id = md5($text);
            exec('cat ' . $filename . ' | trans ' . $origin . ':' . $target . ' -download-audio-as ' . WORKING_DIR . 'temp/' . $record_id . '.ts | sed -r "s/\x1B\[([0-9]{1,2}(;[0-9]{1,2})?)?[m|K]//g"', $out);
            if (!file_exists(WORKING_DIR . "temp/" . $record_id . ".ts")) {
                return ["status" => 198, "err_message" => "translate-shell API is unavailable."];
            }
            $reply = [
                "status" => 200,
                "err_message" => "ok",
                "content" => trim(implode("\n", $out)),
                "record_id" => $record_id
            ];
            return $reply;
        } elseif ($need_voice === true && $no_translate === true) {
            $record_id = md5($text);
            exec('cat ' . $filename . ' | trans ' . $origin . ':' . $target . ' -no-translate -download-audio-as ' . WORKING_DIR . 'temp/' . $record_id . '.ts | sed -r "s/\x1B\[([0-9]{1,2}(;[0-9]{1,2})?)?[m|K]//g"', $out);
            if (!file_exists(WORKING_DIR . "temp/" . $record_id . ".ts")) {
                return ["status" => 198, "err_message" => "translate-shell API is unavailable."];
            }
            $reply = [
                "status" => 200,
                "err_message" => "ok",
                "content" => "",
                "record_id" => $record_id
            ];
            return $reply;
        } else {
            exec('cat ' . $filename . ' | trans ' . $origin . ':' . $target . ' | sed -r "s/\x1B\[([0-9]{1,2}(;[0-9]{1,2})?)?[m|K]//g"', $out);
            $out = trim(implode("\n", $out));
            if ($out == "") {
                return ["status" => 197, "err_message" => "translate-shell API is unavailable."];
            }
            $reply = [
                "status" => 200,
                "err_message" => "ok",
                "content" => $out
            ];
            return $reply;
        }
    }

    public function isMd5($md5) {
        if (trim(strlen($md5)) != 32) return false;
        for ($i = 0; $i < 32; $i++) {
            if (!in_array(substr($md5, $i, 1), ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f'])) return false;
        }
        return true;
    }
}