<?php

spl_autoload_register(function ($class_name) {
    if(file_exists(__DIR__ . "/Utils/" . str_replace("Utils\\", "", $class_name) . ".php")) {
        require_once (__DIR__ . "/Utils/" . str_replace("Utils\\", "", $class_name) . ".php");
    }
});

spl_autoload_register(function ($class_name) {
    if(file_exists(__DIR__ . "/Agents/" . str_replace("Agents\\", "", $class_name) . ".php")) {
        require_once (__DIR__ . "/Agents/" . str_replace("Agents\\", "", $class_name) . ".php");
    }
});

spl_autoload_register(function ($class_name) {
    include ($class_name . ".php");
});

class middleware_rejection_handler
{
    private $SECRET;
    private $DATA;
    private $FD;
    private $SERVER;
    private $DB;
    private $RUN_TYPE;

    public function __construct($secret, $data, $fd, $server, $db, $run_type)
    {
        $this->SECRET = $secret;
        $this->DATA = $data;
        $this->FD = $fd;
        $this->SERVER = $server;
        $this->DB = $db;
        $this->RUN_TYPE = $run_type;
    }

    public function run()
    {
        $error_data = $this->DATA['data'];
        $this->log_reject();
        $send_data = [
            "api_status" => true,
            "api_success" => false,
            "message_type" => "error",
            "error_code" => $error_data['status'],
            "error_message" => $error_data['message'],
        ];
        $this->SERVER->send($this->FD, json_encode($send_data));
        if($error_data['kill']) {
            $this->kill_connection();
        }
    }

    private function log_reject()  {
        $logger = new Utils\Logging_system();
        print("Connection Rejected to {$this->FD}\n");
        $logger->log("Connection Rejected to {$this->FD}", $this->FD, "Middleware Error");
    }

    private function kill_connection() {
        $this->SERVER->close($this->FD);
    }
}