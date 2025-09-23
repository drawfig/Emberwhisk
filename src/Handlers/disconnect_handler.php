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

class disconnect_handler {
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

    public function run() {
        $this->remove_connection($this->FD);
        $this->disconnection_alert();
    }
    private function disconnection_alert() {
        print("Connection closed: {$this->FD}\n");
    }

    private function remove_connection($fd) {
        $db = new Utils\Sqlite_Handler();
        $query = "SELECT * FROM Connections WHERE FD = :fd";
        $vals_array = [
            [
                "name" => ":fd",
                "value" => $fd,
                "type" => "i"
            ]
        ];
        $resp = $db->make_query("select", $query, $vals_array);

        if($resp) {
            $query = "DELETE FROM Connections WHERE FD = :fd";

            $db->make_query("delete", $query, $vals_array);
        }
        $db = null;
    }
}