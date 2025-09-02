<?php
namespace Utils;

spl_autoload_register(function ($class_name) {
	include ($class_name . ".php");
});

class Authentication_System {
	public function authenticate($fd, $user_id, $auth, $data, $server, $db) {
		$query = "SELECT token FROM Connections WHERE FD = :fd";
		$vals_array = [
			[
				"name" => ":fd",
				"value" => $fd,
				"type" => "i"
			]
		];

		$resp = $db->make_query("select", $query, $vals_array);
		$db = null;

		if(hash('sha256', $resp[0]['token'] . json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)) == $auth) {
			return true;
		}
		$server->push($fd, json_encode(["status" => false, "message" => "Unauthorized"]));
		echo "Unauthorized Access \n";
        $logger = new Logging_system();
        $logger->log("Unauthorized Access", $user_id, "Authentication Error");
        $server->close($fd);
        die();
	}
}