<?php
namespace Middleware;

spl_autoload_register(function ($class_name) {
    if(file_exists(__DIR__ . "/Utils/" . str_replace("Utils\\", "", $class_name) . ".php")) {
        require_once (__DIR__ . "/Utils/" . str_replace("Utils\\", "", $class_name) . ".php");
    }
});

spl_autoload_register(function ($class_name) {
    include ($class_name . ".php");
});

class Authenticate_User {
    private $AUTH_ORIGIN;
    private $AUTH_ADDRESS;
    private $RUN_TYPE;
    private $API_KEY;
    public function __construct($run_type) {
        $this->RUN_TYPE = $run_type;
        $env_boot = new Utils\EnvBoostrap($run_type);
        $this->AUTH_ORIGIN = $env_boot->get_var("auth_origin");
        $this->AUTH_ADDRESS = $env_boot->get_var("auth_address");
        $this->API_KEY = $env_boot->get_var("api_key");
    }

    public function run($data, $server, $db, $routing, $fd) {
        if($routing['protected']) {
            switch ($this->AUTH_ORIGIN) {
                case "mysql":
                    $output = $this->mysql_authentication($data, $fd);
                    break;
                case "api":
                    $output = $this->api_authentication($data, $fd);
                    break;
                case "sqlite":
                default:
                    $output =  $this->sqlite_authentication($data, $db, $fd);
            }

            if($output) {
                return true;
            }
            return ["status" => false, "data" => [
                "status" => 401,
                "message" => "Error: Unauthorized",
                "kill_connection" => false,
            ]];
        }
        else {
            return true;
        }
    }

    private function mysql_authentication($data, $fd) {
        $db = new Utils\mysql_handler($this->RUN_TYPE);
        $query = "SELECT token FROM connections WHERE FD = :fd";
        $val_array = [
            [
                "name" => ":fd",
                "value" => $fd,
                "type" => "i"
            ]
        ];
        $resp = $db->make_query("select", $query, $val_array);
        $db = null;

        return $this->hash_check($resp, $data);
    }

    private function sqlite_authentication($data, $db, $fd) {
        $query = "SELECT token FROM connections WHERE FD = :fd";
        $val_array = [
            [
                "name" => ":fd",
                "value" => $fd,
                "type" => "i"
            ]
        ];
        $resp = $db->make_query("select", $query, $val_array);

        return $this->hash_check($resp, $data);
    }

    private function api_authentication($data, $fd) {
        $fetch = new Utils\api_fetch($data, "authenticate_user", $this->RUN_TYPE);
        $resp = $fetch->send($this->AUTH_ADDRESS);
        if(hash('sha256', $this->API_KEY . json_encode($resp['data'],  JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)) === $resp['auth']) {
            return $this->hash_check($resp['data'], $data);
        }
        return false;
    }

    private function hash_check($resp, $raw_data) {
        $data = $raw_data['data'];
        if(hash("sha256", $resp[0]['token'] . json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)) === $raw_data['auth']) {
            return true;
        }
        return false;
    }
}
