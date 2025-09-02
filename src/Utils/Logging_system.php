<?php

namespace Utils;

use Sqlite_DB;

spl_autoload_register(function ($class_name) {
    include ($class_name . ".php");
});

class Logging_system {
    public function log($message, $user_id, $type) {
        $db = new Sqlite_Handler();

        $timestamp = round(microtime(true) * 1000);
        $query = "INSERT INTO server_log (user_id, description, time_entered, message_type) VALUES (:user_id, :message, :timestamp, :type)";
        $vals_array = [
            [
                "name" => ":user_id",
                "value" => $user_id,
                "type" => "i"
            ],
            [
                "name" => ":message",
                "value" => $message,
                "type" => "s"
            ],
            [
                "name" => ":timestamp",
                "value" => $timestamp,
                "type" => "i"
            ],
            [
                "name" => ":type",
                "value" => $type,
                "type" => "s"
            ]
        ];
        $db->make_query("insert", $query, $vals_array);
        $db = null;
    }
}