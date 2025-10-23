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

class Authorization {
    private $ROUTE_SECURITY = [
        "example_route" => 3
    ];

    private $RUN_TYPE;
    private $AUTH_ORIGIN;
    private $AUTHORIZATION_ADDRESS;
    public function __construct($run_type) {
        $this->RUN_TYPE = $run_type;
        $env_boot = new Utils\EnvBoostrap($run_type);
        $this->AUTH_ORIGIN = $env_boot->get_var("auth_origin");
        $this->AUTHORIZATION_ADDRESS = $env_boot->get_var("authorization_address");
    }
    public function run($data, $server, $db, $routing, $fd) {
        if(array_key_exists($data['message_type'], $this->ROUTE_SECURITY)) {
            switch ($this->AUTH_ORIGIN) {
                case "mysql":
                    $output = $this->mysql_authorization($data);
                    break;
                case "api":
                    $output = $this->api_authorization($data);
                    break;
                case "sqlite":
                default:
                    $output =  $this->sqlite_authorization($data, $db);
            }

            if($output <= $this->ROUTE_SECURITY[$data['message_type']]) {
                return true;
            }
            else {
                return ["status" => false, "data" => [
                    "status" => 403,
                    "message" => "Error: Forbidden",
                    "kill_connection" => false,
                ]];
            }
        }
        else {
            return true;
        }
    }

    private function api_authorization($data) {
        $fetch = new Utils\api_fetch($data, "authenticate_user", $this->RUN_TYPE);
        $resp = $fetch->send($this->AUTHORIZATION_ADDRESS);
        $data = $resp['data'];

        return $data["auth_level"];
    }

    private function mysql_authorization($data) {
        $db = new Utils\mysql_handler($this->RUN_TYPE);
        $query = "SELECT auth_level FROM users WHERE user_id = :user_id";
        $val_array = [
            [
                "name" => ":user_id",
                "value" => $data['user_id'],
                "type" => "i"
            ]
        ];
        $resp = $db->make_query("select", $query, $val_array);
        $db = null;
        return $resp[0]['auth_level'];
    }

    private function sqlite_authorization($data, $db) {
        $query = "SELECT auth_level FROM users WHERE user_id = :user_id";
        $val_array = [
            [
                "name" => ":user_id",
                "value" => $data['user_id'],
                "type" => "i"
            ]
        ];
        $resp = $db->make_query("select", $query, $val_array);
        return $resp[0]['auth_level'];
    }
}