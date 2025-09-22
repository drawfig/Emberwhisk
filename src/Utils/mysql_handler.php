<?php

namespace Utils;
use PDO;

spl_autoload_register(function ($class_name) {
    include ($class_name . ".php");
});

class mysql_handler {
    private $db_host;
    private $db_port;
    private $db_username;
    private $db_password;
    private $db_name;

    private $mysql_run;
    private $db;

    public function __construct($run_type) {
        $this->init($run_type);
    }

    private function init($run_type) {
        $property_provider = new EnvBootstrap($run_type);
        $this->mysql_run = $property_provider->get_var("mysql_run");
        if($this->mysql_run) {
            $this->db_host = $property_provider->get_var("db_host");
            $this->db_port = $property_provider->get_var("db_port");
            $this->db_username = $property_provider->get_var("db_username");;
            $this->db_password = $property_provider->get_var("db_password");
            $this->db_name = $property_provider->get_var("db_name");
        }
    }

    public function make_query($type, $query, $var_array) {
        $log = new Logging_system();
        if($this->mysql_run) {
            try {
                $this->db = new \PDO("mysql:host={$this->db_host};port={$this->db_port};dbname=" . $this->db_name, $this->db_username, $this->db_password);
                switch ($type) {
                    case "insert":
                        $output = $this->insert_query($query, $var_array);
                        break;
                    case "delete":
                        $output = $this->delete_query($query, $var_array);
                        break;
                    case "update":
                        $output = $this->update_query($query, $var_array);
                        break;
                    case "select":
                    default:
                        $output = $this->basic_query($query, $var_array);
                }
                return $output;
            } catch (\PDOException $e) {
                $log->log("Failed to connect to database", null, "error");
                $db = null;
                return false;
            }
        }
        else {
            $log->log("Mysql module has been disabled, if you want to use mysql with your server please update your config.", null, "Warning");
            return [];
        }
    }
    private function insert_query($query, $val_array) {
        $ready_query = $this->db->prepare($query);
        foreach($val_array as $val) {
            $ready_query->bindeValue($val["name"], $val["value"], $this->pdo_type_sort($val["type"]));
        }

        $ready_query->execute();
        return $this->db->lastInsertId();
    }

    private function basic_query($query, $val_array) {
        $ready_query = $this->db->prepare($query);
        if($val_array) {
            foreach ($val_array as $val) {
                $ready_query->bindValue($val["name"], $val["value"], $this->pdo_type_sort($val["type"]));
            }
        }
        $ready_query->execute();
        $output = $ready_query->fetchAll(\PDO::FETCH_ASSOC);
        return $output;
    }

    private function delete_query($query, $val_array) {
        $ready_query = $this->db->prepare($query);
        if($val_array) {
            foreach ($val_array as $val) {
                $ready_query->bindValue($val["name"], $val["value"], $this->pdo_type_sort($val["type"]));
            }
        }
        $ready_query->execute();
        return true;
    }

    private function update_query($query, $val_array)
    {
        $ready_query = $this->DB->prepare($query);
        if($val_array) {
            foreach ($val_array as $val) {
                $ready_query->bindValue($val["name"], $val["value"], $this->pdo_type_sort($val["type"]));
            }
        }
        $ready_query->execute();
        return true;
    }

    private function pdo_type_sort($type) {
        switch ($type) {
            case "i":
                return \PDO::PARAM_INT;
            case "s":
                return \PDO::PARAM_STR;
            case "b":
                return \PDO::PARAM_BOOL;
        }
    }

    public function __destruct() {
        $this->DB = null;
    }
}
