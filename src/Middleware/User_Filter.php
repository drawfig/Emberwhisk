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

class User_Filter
{
    private $TIME_BUFFER;
    private $RATE_LIMIT;

    private $ROUTES_USING_WHITE_LIST = [
        "example_route",
    ];

    public function __construct($run_type)
    {
        $ENV_BOOTSTRAP = new \Utils\EnvBootstrap($run_type);
    }

    public function run($data, $server, $fd)
    {
        $user_ip = $server->getClientInfo($fd)['remote_ip'];
        $whitelist_used = $this->check_for_white_list($data['message_type']);
        if($whitelist_used) {
            $whitelist_output = $this->search_whitelist($user_ip);
            if(sizeof($whitelist_output) > 0) {
                return true;
            }
            else {
                return false;
            }
        }
        else {
            $blacklist_output = $this->search_blacklist($user_ip);
            if(sizeof($blacklist_output) > 0) {
                return false;
            }
            else {
                return true;
            }
        }
    }

    private function check_for_white_list($route_in) {
        if (in_array($route_in, $this->ROUTES_USING_WHITE_LIST)) {
            return true;
        }
        else {
            return false;
        }
    }

    private function search_whitelist($ip) {
        $db = new \Utils\sqlite_handler();
        $query = "SELECT * FROM white_list WHERE ip = :ip";
        $vals_array = [
            [
                "name" => ":ip",
                "value" => $ip,
                "type" => "s"
            ]
        ];
        $output = $db->make_query("select", $query, $vals_array);

        $db = null;
        return $output;
    }

    private function search_blacklist($ip) {
        $db = new \Utils\sqlite_handler();
        $query = "SELECT * FROM black_list WHERE ip = :ip";
        $vals_array = [
            [
                "name" => ":ip",
                "value" => $ip,
                "type" => "s"
            ]
        ];
        $output = $db->make_query("select", $query, $vals_array);
        $db = null;
        return $output;
    }

}