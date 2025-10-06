<?php
namespace Middleware;

class Middleware_Software_Groups {
    public $GLOBAL_MIDDLEWARE = [
        "Rate_Limiter",
        "User_Filter",
    ];

    public $REGIONAL_MIDDLEWARE = [
        "example_region" => [ "GROUP:example_group" ],
    ];

    public $LOCAL_GROUP_MIDDLEWARE = [
        "example_group" => [ "Example_Middleware" ],
    ];
}