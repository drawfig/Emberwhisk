<?php
namespace Utils;
use Sqlite_DB;

class Sqlite_Handler {
	private $DB;

	public function __construct() {
		include_once("Sqlite_DB.php");
		$this->DB = new Sqlite_DB();
	}

	public function make_query($type, $query, $val_array) {
		switch ($type) {
			case "insert":
				return $this->insert_query($query, $val_array);
			case "delete":
				return $this->delete_query($query, $val_array);
            case "update":
                return $this->update_query($query, $val_array);
			case "select":
			default:
				return $this->basic_query($query, $val_array);
		}
	}

	private function basic_query($query, $val_array) {
		$ready_query = $this->DB->prepare($query);
		if($val_array) {
			foreach ($val_array as $val) {
				$ready_query->bindValue($val["name"], $val["value"], $this->pdo_type_sort($val["type"]));
			}
		}
		$output = [];
		$run_query = $ready_query->execute();
		while ($res = $run_query->fetchArray(SQLITE3_ASSOC)) {
			$output[] = $res;
		}
		return $output;

	}

	private function insert_query($query, $val_array) {
		$ready_query = $this->DB->prepare($query);
		foreach($val_array as $val) {
			$ready_query->bindValue($val["name"], $val["value"], $this->pdo_type_sort($val["type"]));
		}

		$ready_query->execute();
		return $this->DB->lastInsertRowID();
	}

	private function delete_query($query, $val_array) {
		$ready_query = $this->DB->prepare($query);
		if($val_array) {
			foreach ($val_array as $val) {
				$ready_query->bindValue($val["name"], $val["value"], $this->pdo_type_sort($val["type"]));
			}
		}

		$ready_query->execute();
		return true;
	}

    private function update_query($query, $val_array) {
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
				return SQLITE3_INTEGER;
			case "f":
				return SQLITE3_FLOAT;
			case "b":
				return SQLITE3_BLOB;
			case "n":
				return SQLITE3_NULL;
			case "t":
			case "s":
			default:
				return SQLITE3_TEXT;
		}
	}

	public function __destruct() {
		$this->DB->close();
		$this->DB = null;
	}
}