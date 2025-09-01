<?php
spl_autoload_register(function ($class_name) {
    if(file_exists(__DIR__ . "/Utils/" . str_replace("Utils\\", "", $class_name) . ".php")) {
        require_once (__DIR__ . "/Utils/" . str_replace("Utils\\", "", $class_name) . ".php");
    }
});

spl_autoload_register(function ($class_name) {
    include ($class_name . ".php");
});

class test_handler {
    private $SECRET;

    public function __construct($secret) {
        $this->SECRET = $secret;
    }

    public function test_method() {
        print("it works chief \n");
    }
}