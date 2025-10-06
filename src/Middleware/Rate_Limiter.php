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

class Rate_Limiter {
    private $TIME_BUFFER;
    private $RATE_LIMIT;

    public function __construct($run_type) {
        $ENV_BOOTSTRAP = new \Utils\EnvBootstrap($run_type);
        $this->TIME_BUFFER = $ENV_BOOTSTRAP->get_var("time_buffer");
        $this->RATE_LIMIT = $ENV_BOOTSTRAP->get_var("rate_limit");
    }

    public function run($data, $server, $fd) {
        $out_chk = $this->check_rate($data, $server, $fd);
        if($out_chk) {
            return true;
        }
        else {
            $this->log_rejection($fd);
            return false;
        }
    }

    public function check_rate($data, $server, $fd) {
        $db = new \Utils\sqlite_handler();
        $query = "SELECT * FROM rate_tracking WHERE fd = :fd";
        $vals_array = [
            [
                "name" => ":fd",
                "value" => $fd,
                "type" => "i"
            ]
        ];
        $resp = $db->make_query("select", $query, $vals_array);
        $current_time = (int) round(microtime(true) * 1000);

        if($resp) {
            if($resp[0]['timestamp'] + (int) $this->TIME_BUFFER >= $current_time) {
                if($resp[0]['uses'] >= (int) $this->RATE_LIMIT) {
                    $db = null;
                    return false;
                }
                else {
                    $query = "UPDATE rate_tracking SET uses = :uses WHERE fd = :fd";
                    $vals_array = [
                        [
                            "name" => ":uses",
                            "value" => $resp[0]['uses'] + 1,
                            "type" => "i"
                        ],
                        [
                            "name" => ":fd",
                            "value" => $fd,
                            "type" => "i"
                        ]
                    ];
                    $db->make_query("update", $query, $vals_array);
                    $db = null;
                    return true;
                }
            }
            else {
                $query = "UPDATE rate_tracking SET timestamp = :timestamp, uses = :uses WHERE fd = :fd";
                $vals_array = [
                    [
                        "name" => ":timestamp",
                        "value" => $current_time,
                        "type" => "i"
                    ],
                    [
                        "name" => ":uses",
                        "value" => 1,
                        "type" => "i"
                    ],
                    [
                        "name" => ":fd",
                        "value" => $fd,
                        "type" => "i"
                    ]
                ];
                $db->make_query("update", $query, $vals_array);
                $db = null;
                return true;
            }
        }
        else {
            $query = "INSERT INTO rate_tracking (fd, timestamp, uses) VALUES (:fd, :timestamp, :uses)";
            $vals_array = [
                [
                    "name" => ":fd",
                    "value" => $fd,
                    "type" => "i"
                ],
                [
                    "name" => ":timestamp",
                    "value" => $current_time,
                    "type" => "i"
                ],
                [
                    "name" => ":uses",
                    "value" => 1,
                    "type" => "i"
                ]
            ];
            $db->make_query("insert", $query, $vals_array);
            $db = null;
            return true;
        }
    }

    private function log_rejection($fd) {
        $logger = new \Utils\Logging_system();
        $logger->log("Connection Rejected to {$fd} Rate has been Exceeded.", $fd, "Middleware Error");
    }
}
