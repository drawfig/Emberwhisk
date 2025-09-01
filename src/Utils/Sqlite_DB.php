<?php
class Sqlite_DB extends SQLite3 {
	public function __construct() {

		$this->open('web_sock.db');
	}
}