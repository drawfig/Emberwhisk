<?php

spl_autoload_register(function ($class_name) {
    if(file_exists(__DIR__ . "/Utils/" . str_replace("Utils\\", "", $class_name) . ".php")) {
        require_once (__DIR__ . "/Utils/" . str_replace("Utils\\", "", $class_name) . ".php");
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

    public function __construct($secret, $data, $fd, $server, $db) {
        $this->SECRET = $secret;
        $this->DATA = $data;
        $this->FD = $fd;
        $this->SERVER = $server;
        $this->DB = $db;
    }

    public function bounce() {
        print("Boing!\n");
        $this->SERVER->push($this->FD, json_encode($this->DATA));
    }
}