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

class Format_Check {
    private $ERROR_MESS = [
        "status" => false,
        "data" => [
            "status" => 400,
            "message" => "Error: Bad Request",
            "kill_connection" => false,
        ]
    ];

    private $REQUEST_FEILDS = [
        "user_id",
        "message_type",
        "data",
        "auth"
    ];

    private $AUTH_ORIGIN;
    private $AUTH_ADDRESS;
    private $RUN_TYPE;
    private $API_KEY;
    public function __construct($run_type) {
        $this->RUN_TYPE = $run_type;
        $env_boot = new \Utils\EnvBootstrap($run_type);
        $this->AUTH_ORIGIN = $env_boot->get_var("auth_origin");
        $this->AUTH_ADDRESS = $env_boot->get_var("auth_address");
        $this->API_KEY = $env_boot->get_var("api_key");
    }

    public function run($data, $server, $db, $routing) {
        if(sizeof($data) !== 4) {
            return $this->ERROR_MESS;
        }
        foreach($data as $key => $value) {
            if (!in_array($key, $this->REQUEST_FEILDS)) {
                return $this->ERROR_MESS;
            }
        }
        if(gettype($data['user_id']) === "integer" && gettype($data['auth']) === "string" && gettype($data['data']) === "array") {
            return true;
        }
        else {
            return $this->ERROR_MESS;
        }
    }
}