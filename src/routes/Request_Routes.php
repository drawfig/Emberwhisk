<?php

class Request_Routes {
    public $REQUEST_ROUTES = [
        "test" => ["class" => "test_handler", "method" => "test_method", "protected" => false],
        "secure_test" => ["class" => "test_handler", "method" => "test_secure_method", "protected" => true]
    ];
}