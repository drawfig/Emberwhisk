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

class Example_Middleware {
    private $RUN_TYPE;

    public function __construct($run_type) {
        $this->RUN_TYPE = $run_type;
    }

    public function run($data, $server, $db, $routing, $fd) {
        print("Example Middleware\n");
    }
}