<?php
namespace Utils;

spl_autoload_register(function ($class_name) {
	include ($class_name . ".php");
});

class api_fetch
{
	private $PAYLOAD;
	private $API_KEY;
	private $API_PROTOCOL;
	private $API_ADDRESS;
    private $API_VERSION;

	public function __construct($data, $action, $environment) {
		$this->property_setter($environment);
		$this->PAYLOAD = [
			'user_id' => $data["user_id"],
			'api_version' => $this->API_VERSION,
			'action' => $action,
			'data' => $data,
			'auth' => $data["auth"]
		];
	}

	private function property_setter($environment) {
		$prop_get = new \Utils\EnvBootstrap($environment);
		$this->API_KEY = $prop_get->get_var("api_key");
		$this->API_PROTOCOL = $prop_get->get_var("api_protocol");
		$this->API_ADDRESS = $prop_get->get_var("api_address");
        $this->API_VERSION = $prop_get->get_var("api_version");
	}

	private function api_auth_gen($data, $auth) {
		return hash('sha256', $this->API_KEY . json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
	}

	public function send($route) {
		$url = $this->API_PROTOCOL . "://" . $this->API_ADDRESS . "/" . $route;
		$out_payload = json_encode($this->PAYLOAD);

		$options = [
			$this->API_PROTOCOL => [
				'header' => "Content-Type: application/x-www-form-urlencoded\r\n" . "Content-length: " . strlen($out_payload) . "\r\n",
				'method' => 'POST',
				'content' => $out_payload
			]
		];

		$context = stream_context_create($options);
		$response = file_get_contents($url, false, $context);
		return json_decode($response, true);
	}
}