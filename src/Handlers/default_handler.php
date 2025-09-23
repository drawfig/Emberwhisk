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

class default_handler {
    private $SECRET;
    private $DATA;
    private $FD;
    private $SERVER;
    private $DB;
    private $RUN_TYPE;

    public function __construct($secret, $data, $fd, $server, $db, $run_type) {
        $this->SECRET = $secret;
        $this->DATA = $data;
        $this->FD = $fd;
        $this->SERVER = $server;
        $this->DB = $db;
        $this->RUN_TYPE = $run_type;
    }

    public function bounce() {
        $agent = new Agents\default_agent();
        print($agent->bounce_txt());
        $this->SERVER->push($this->FD, json_encode($this->DATA));
    }
}