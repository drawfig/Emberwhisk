<?php
namespace Utils;
include_once realpath(__DIR__ . "/../../vendor/autoload.php");

class EnvBootstrap
{
	private $env;
	private $address;
	private $port;
	private $protocol;
	private $environment;
	private $api_address;
	private $api_key;
	private $api_protocol;
	private $daemonization;
	private $worker_count;
	private $ssl_cert;
	private $ssl_key;
	private $ssl_verify_peer;
	private $ssl_allow_self_signed;
	private $db_host;
	private $db_port;
	private $db_name;
	private $db_username;
	private $db_pass;
	private $secret;

	public function __construct($run_type) {
		$this->setEnvironment($run_type);
		$this->init();
	}

	private function setEnvironment($env_arg) {
		$this->env = "local";
		if(!!$env_arg) {
			switch ($env_arg) {
				case "local":
				case "dev":
				case "test":
				case "prod":
					$this->env = $env_arg;
					break;
			}
		}
	}

	private function init() {
		$dotenv = \Dotenv\Dotenv::createImmutable(realpath(__DIR__ . "/../"), ".env.{$this->env}");
		$dotenv->load();
		$this->address = $_ENV["ADDRESS"];
		$this->port = $_ENV["PORT"];
		$this->protocol = $_ENV["PROTOCOL"];
		$this->api_address = $_ENV["API_ADDRESS"];
		$this->environment = $_ENV["ENVIRONMENT"];
		$this->api_key = $_ENV["API_KEY"];
		$this->api_protocol = $_ENV["API_PROTOCOL"];
		$this->daemonization = $_ENV["DAEMONIZATION"];
		$this->worker_count = $_ENV["WORKER_COUNT"];
		$this->ssl_cert = $_ENV["SSL_CERT"];
		$this->ssl_key = $_ENV["SSL_KEY"];
		$this->ssl_verify_peer = $_ENV["SSL_VERIFY_PEER"];
		$this->ssl_allow_self_signed = $_ENV["SSL_ALLOW_SELF_SIGNED"];
		$this->db_host = $_ENV["DB_HOST"];
		$this->db_port = $_ENV["DB_PORT"];
		$this->db_name = $_ENV["DB_NAME"];
		$this->db_username = $_ENV["DB_USERNAME"];
		$this->db_pass = $_ENV["DB_PASSWD"];
		$this->secret = $_ENV["SECRET"];
	}

	public function get_var($var_key) {
		if(isset($this->$var_key)) {
			return $this->$var_key;
		}
		return -99999999;
	}
}