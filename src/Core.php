<?php

spl_autoload_register(function ($class_name) {
	if(file_exists(__DIR__ . "/Utils/" . str_replace("Utils\\", "", $class_name) . ".php")) {
		require_once (__DIR__ . "/Utils/" . str_replace("Utils\\", "", $class_name) . ".php");
	}
});

spl_autoload_register(function ($class_name) {
    if(file_exists(__DIR__ . "/Handlers/" . str_replace("Handlers\\", "", $class_name) . ".php")) {
        require_once (__DIR__ . "/Handlers/" . str_replace("Handlers\\", "", $class_name) . ".php");
    }
});

spl_autoload_register(function ($class_name) {
	include ($class_name . ".php");
});

class Core {
	public $ADDRESS;
	public $PORT;
	public $PROTOCOL;
	public $ENVIRONMENT;
	public $API_ADDRESS;
	public $API_KEY;
	public $API_PROTOCOL;
	public $DAEMONIZATION;
	public $WORKER_COUNT;
	public $SSL_CERT;
	public $SSL_KEY;
	public $SSL_VERIFY_PEER;
	public $SSL_ALLOW_SELF_SIGNED;
	public $DB_HOST;
	public $DB_USERNAME;
	public $DB_PASSWD;
	public $DB_NAME;
	public $DB_PORT;
	public $SECRET;

    public $ROUTES;

	public function __construct($args) {
		if(isset($args[1])) {
			$this->bootstrapEnvironment($args[1]);
		}
		else {
			$this->bootstrapEnvironment('local');
		}
	}

	public function convertBool($string) {
		if($string == "true") {
			return true;
		}
		return false;

	}

	private function bootstrapEnvironment($environment) {
		$EnvBoot = new \Utils\EnvBootstrap($environment);

		$this->ADDRESS = $EnvBoot->get_var("address");
		$this->PORT = $EnvBoot->get_var("port");
		$this->PROTOCOL = $EnvBoot->get_var("protocol");
		$this->ENVIRONMENT = $EnvBoot->get_var("environment");
		$this->API_ADDRESS = $EnvBoot->get_var("api_address");
		$this->API_KEY = $EnvBoot->get_var("api_key");
		$this->API_PROTOCOL = $EnvBoot->get_var("api_protocol");
		$this->DAEMONIZATION = $EnvBoot->get_var("daemonization");
		$this->WORKER_COUNT = $EnvBoot->get_var("worker_count");
		$this->SSL_CERT = $EnvBoot->get_var("ssl_cert");
		$this->SSL_KEY = $EnvBoot->get_var("ssl_key");
		$this->SSL_VERIFY_PEER = $EnvBoot->get_var("ssl_verify_peer");
		$this->SSL_ALLOW_SELF_SIGNED = $EnvBoot->get_var("ssl_allow_self_signed");
		$this->DB_HOST = $EnvBoot->get_var("db_host");
		$this->DB_USERNAME = $EnvBoot->get_var("db_username");
		$this->DB_PASSWD = $EnvBoot->get_var("db_password");
		$this->DB_NAME = $EnvBoot->get_var("db_name");
		$this->DB_PORT = $EnvBoot->get_var("db_port");
		$this->SECRET = $EnvBoot->get_var("secret");

        $this->init_routes();
	}

	protected function send_handshake($server, $fd) {
		$db = new Utils\Sqlite_Handler();

		$random_str = bin2hex(random_bytes(32));
		$query = "INSERT INTO random_str_store (FD, random_string) VALUES (:fd, :random_string)";
		$vals_array = [
			[
				"name" => ":fd",
				"value" => $fd,
				"type" => "i"
			],
			[
				"name" => ":random_string",
				"value" => $random_str,
				"type" => "s"
			]
		];

		$db->make_query("insert", $query, $vals_array);
		$db = null;

		$data = [
			'handshake_rng' => $random_str,
		];

		$payload =[
			'type' => "handshake",
			'data' => $data,
			'auth' => $this->auth_gen($data)
		];

		$server->push($fd, json_encode($payload));
	}

	private function ascii_out() {
		print(
			"                                                                                                                                                       
                                                                                                                                                       
                                                                                                                                                       
                                                                                                                                                       
                                                                                                                                                       
                                                                                                                                                       
                                                                                                                                                       
                                                                                                                                                       
                                                                                                                                                       
                                                                                                                                                       
                                                                                                                                                       
                                                                                                                                                       
                                                                                                                                                       
                                                  &&&&&&&&                                                                                             
                                               &&&$::::::x&&&&&&                                                                                       
                                              &&x::::::::::::::x&&&&&                                                                                  
                                            &&x:::::::::::::::::;;+;;$&&&                                                                              
                                          &&x:::::::::::::::::::::::::::$&&                                                                            
                                        &&x:::::::::::::::::::::::::::::::$&&                                                                          
                              &&&     &&$:::::::;:::::::::::::::::::::::::::$&&                     &&                                                 
                              &&&&&&&&&:::::::;$::::::::::::::::::::::::::::::x&&&                &&&&&                                                
                             &&&&&&&&&&X;:::::&:::::::::::::::::::::::::::::::::;&&&             &&&&&&&                                               
                             &&&&:x&&&&&&&&;:&X::::::::::::::::::::::::::::::::::::x&&&        &&&&&&&&&&                                              
                             &&&&::::X&&&&&&&&X:::::::::::::::::::::::::::::::::::::::x&&&&   &&&&&&&&&&&                                              
                             &&&&::::::x&&&&&&&&$;:::::::::::::::::::::::::;;;;::::::::::x&  &&&&&&&&&&&&                                              
                            &&&&&::::::::x&&&&&&&&&x;:::::::::::::::::::X;:;+xxxxxX\$xx+;::&&&&&&&&&&&&&&&&                                             
                 &&&&      &X:&&&;:::::::::$&&&&&&;:x&x:::::::::::::::::x:++:::::::x:+;:::x$&$;:::x$&&&&&&                                             
               &&x::;$&& &$:::$&&$::::::::::;&&&&&$::::x$;::::::::;x$\$x:;$:+\$xx:::::x;+;:::::xx:::::::;$&&&          &&&&&&&&&&&&                      
              &$:::::::+$;::::;&&&;:::::x+::::x&;:;::::::;\$x::+$\$x::::::::$:xx:::::::;;;:::::::xX::::::::$&&&$\$Xx;::::;;;+xxxxxxx++xx$$&&&             
            &&x::::::::::::::::&&&x:::::x::x:::;&;::::::::::X&;::::::::::::x:X+:;;++xxX;x::::::::Xx;+x\$X+:::::;+x$&&&&&&&&&&&&&&&&&&&&&&&xxX$&&        
           &&;:::::::::x:::::::x&&&:::::x;:::;:::$::::::::::::x$;:::::::::::$:::;;+xxxxXx$&&&&&&$\$x::::::;x&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&xX&&     
          &&:::::::+;::;::::::::&&&$:::::x;:::::::$:::::::::::::x&;:x++x&&&\$x;;:::::::;xXXx;::::::::;x$&&&&&&x$&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&   
         &&:::::;x&&&x::::::::::+&&&x:::::$;:::::::x::::::::::::::x&;:;&+::::::::;;x;:::::::::::;x&&&&$\$xx;::x&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&  
        &$::::x&&   &&&x::::::::x&&&&+:::::x;:::::::x::::::::::::::xx:::$:::::::::::::::::::;x&&\$x::::::::::X&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&& 
        &+;X&&        &&&x:::;$&&&&&&&;::;xx$+:::::::x;::::::::::+X::::x::::::::::::::::;x&&$+::::::::::::x$$$$&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&& 
         &               &&&&&&&  &&&&&;:x;::::::::::::x:::::::xX:::;+:::::::::::::::+&&&&+::::::::::::::::::::::;$&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&& 
                                   &x::+x:;x:::::::::::::;::;X+::;;::::::::::::::;$&$+::::;:::::::::::::::::::::::::x&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&& 
                                   &&+::;X:::xx::::::::::;xx::::::::::::::::::x&&x::::::::::::::::::::::::::::::::::::$&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&  
                                     &;:::$;:;xxx::::::xX:::::::::::::::::+$&X;::::::::::::::::::::::::::::::::::::::::x&&&&&&&&&&&&&&&&&&&&&&&&&&&&   
                                     &&;:::X$:::::::xx;::::::::::::::::x&$+:::::::::::::::::::::::::::::::::::;;;:::::::x&&&&&&&&&&&&&&&&&&&&&&&&&&&   
                                      &&x:::;&x::+x;:::::::::::::::;$&x;+xx+;:::::::::::::::::::::;::::::::::::::+&\$x::::X&&&&&&&&&&&&&&&&&&&&&&&&     
                                       &&X::::;X;:::::::::::::::x&$;::::::::xX&&&&&&&;:;:::::::::x$\$x::::::::::::::;&&&$+:&&&&&&&&&&&&&&&&&&&&&&&      
                                     &&&+x&+:;::::::::::::::;$&x::::::::::::::::;x&$;:::::::::::x&&&&$:::::::::::::::&&&&&&&&&&&&&&&&&&&&&&&&&&&       
                                    &&x::::::::::::::::::x$$;::::::::::::::::::::::::::::::::::::::::\$x::::::::::::::;&&&&&&&&&&&&&&&&&&&&&&&&         
                                    &&$::::::::::::::;X&&;:::::::::::::::::::::::::::::::::::::::::::;&::::;::::::::::&&&&&&&&&&&&&&&&&&&&&&&          
                                 &&&x;::::::::::::+$&&&$::::::::;:::::::::::::::::::::::::::::::::::::$&x:::x:::::::::&&&&&&&&&&&&&&&&&&&&&            
                              &&&X;:::::::::::;x&&&&&&$::::::;x++&\$x++;;::::::::::::::::::::::::::::::+;&&;::x:::::::;&&&&&&&&&&&&&&&&&&&              
                            &&&+:::::::::::+$&&&&&&&&x:::::::::&&&\$X$&&&&&&&&X;::::::::::::::::::::::::;&&&x:&$::::::&&&&&&&&&&&&&&&&&&                
                         &&&X:::::::::::x&&&&&&&&x;:::::::::::::x&+:&&&xXX::;x&&\$x:::::::::::::::::::::x&&&&x&&&;:::x&&&&&&&&&&&&&&&&                  
                       &&$+:::::::::;X&&&&&&&&&&&&+:::::::::::::::;;&&&&&$:::::;+::::::::::::::::::::::$&&&&&&&&&::;&&&&&&&&&&&&&&&                    
                   &&&&x:::::::::x$&&&&&&&&&&&&x:::::::::::::::::::::::::;;;;:::::::::::::::;$$\$XXxx;:;&&&&&&&&&&x:&&&&&&&&&&&&&                       
                &&&$+::::::::;x&&&&&&&&&&&&&&;::::::::::::::::::::::::::::::::::::::::::::::x&&&&;:x&&&&&&&&&&&&&&&&&&&&&&&&&                          
          &&&&&X;::::::::;x&&&&&&&&&&&&&&&&&&&;:::::::::::::::::::::::::::::::::::::::::::::::x\$x::;&;;&&&&&&&&&&&&&&&&&&                              
    &&&&&&x+:::::::+X$&&&&&&&&&&&&&&&&&&&&&&&&&$;:::::::::::::::::::::::::::$$:::::::::::::::::$&\$x+;x:;x&&&&&&&&&&&&                                  
 &&&x+;:::;+x$&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&x::::::::::::::::::::::::::$;::::::::::::::::X&&&&&&xX&&&&&&&                                        
  &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&x::::::::::::::::::::::::xx::::::::::::::::$&&&&&::x&                                             
        &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&;;;::::::::::::::::::::::::;x;::::::::::::::;Xx;;:::x&                                            
              &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&\$x;::::::::::::::::::::::::;;xx+::::::::::::;;::::$&                                           
                       &&&&&&&&&&&&&&&&&&&&&&&&           &&&$;::::::::::::::::::::::::::::;;;:::::x::::::$&                                           
                                                              &&&+::::::::::::::::::::::::::::::;;::;+x$&&                                             
                                              &&&&&&&&&&&&&&&&&&&&&::::::::::::::::::::::::::::;;:;x$&&&                                               
                                           &$;::::::::::;+x&&&&&&&&;::::::::::xx;::::::::::;x&&&&&                                                     
                                          &&::::::::::::::::::;x$&&+:::::::::::x&&&&&&\$Xx+;;x&&&                                                       
                                          &&::::+x;::::::::::::::::+&&\$x;::::::::x&&&&&&&&\$x::::x&&                                                    
                                          &&XxX;::::::::::::::::::::::x&&&&&&&&\$xxx&&&&&&&&&&&&+::;&&                                                  
                                          &&x::::::X&X+;;;;xX$;:::::::::;&&&&&&&&&&&&&&&&&&&&&&&x::;&                                                  
                                        &&+:::::+&x::::::::::::;x+:::::::::\$X;;;;;+$&&&&&&&&&&&&;::x&                                                  
                                      &&x:::::;&X::::::::::::::::::+:::::::::x&;::::;;;::&&&&&&x::;&$&&&                                               
                                      &x:::::+&+:::::::::::::::::::::;::::::::::X$::::::;x&&&&x:::\$x:::x&&                                             
                                      &&;:::;&x:::::::::::::::::::::::::::::::::::+$:::::::$&x:::$+;:::::X&&                                           
                                       &&::;&x::::::::::::::::::::::::::::::::::::::+$::::X++:::x:;:::::::x&                                           
                                       &&&:&&::::::::::::::::::::::::::::::;+;::::::::x;+;;;:::::::::::::::x&                                          
                                        &&&&;:::::x:::::::::::::::::;X&&x;::::+&&x:::::x$;xx::$&&x::::::::::&&                                         
                                         &&X::::;x::::::::::::::+&&&&&&&&::::::::;$&x::$::::;x::::x&;:::::::x&                                         
                                         &&;:::x;::::::::::::X&+::+&&&&&&$:::::::::::;x&&x::X:::::::;&x::::::&&                                        
                                         &&:::x:::::::::::X&;::::::x&&&&&&::::::::::::::::x&&&+::::::::\$X::::$&                                        
                                        &&x::x:::::::::x&;:::::::+:;&&&&&&$;:::::::::::::x$::::::xx::::::x&;:x&                                        
                                        &&;;x::::::::\$x:::::::::::+;&:+xxx:x:::::::::::::&::::::::::$+:::::;\$X&                                        
                                        &&x+:::::::&+:::::::;:::::++&:x&&&;x:::::::::::::\$X:::::::::::&;::::::x&                                       
                                       &&&;::::::$;:::::::::;::::::x;x:::::x::::::::::::::$$:::::::::::&+:::::::&&                                     
                                       &&::::::xx:::::::::::;::::::;XX&&&&&:::::::::::::::::&x:::::::::xx::::::::x&                                    
                                     &&&::::::&::::::::::::::+::::::x&&&&&x:::::::::::::::::::x$;:::::;&::::::::::X&                                   
                                     &&:::::+&::::::::::::::::x::::::&&&&&::::::::::::::::::::::::;x$$;::::::::::::$&                                  
                                     =================================EMBERWHISK PROJECT=============================
                                     |                                      2025                                    |
                                     ================================================================================                                 \n"
		);
	}

	private function handle_handshake_resp($data, $fd) {
		$db = new Utils\Sqlite_Handler();

		$query = "SELECT * FROM random_str_store WHERE FD = :fd";
		$vals_array = [
			[
				"name" => ":fd",
				"value" => $fd,
				"type" => "i"
			]
		];
		$resp = $db->make_query("select", $query, $vals_array);

		if(sizeof($resp) > 0  && $resp[0]['random_string'] == $data['sent_rng']) {
			$query = "DELETE FROM random_str_store WHERE FD = :fd";

			$db->make_query("delete", $query, $vals_array);
			$resp = $this->get_user_api_key($data['user_id']);

			$this->add_connection($fd, $resp['data']['api_token'], $db);
		}
		else {
			echo "Rejected Connection to {$fd} \n";
		}
		$db = null;
	}

    private function init_scoreboard_connection($server, $fd) {
        $db = new Utils\Sqlite_Handler();

        $query = "SELECT * FROM scoreboard_connection WHERE fd = :fd";
        $vals_array = [
            [
                "name" => ":fd",
                "value" => $fd,
                "type" => "i"
            ]
        ];

        $check = $db->make_query("select", $query, $vals_array);

        if(sizeof($check) <= 0) {
            $random_str = bin2hex(random_bytes(32));

            $query = "INSERT INTO scoreboard_connection (fd, random_val) VALUES (:fd, :random_string)";

            $vals_array = [
                [
                    "name" => ":fd",
                    "value" => $fd,
                    "type" => "i"
                ],
                [
                    "name" => ":random_string",
                    "value" => $random_str,
                    "type" => "s"
                ]
            ];

            $db->make_query("insert", $query, $vals_array);
            $db = null;
        }
        else {
            $random_str = $check[0]['random_val'];
        }

        $data = [
            'random_key' => $random_str,
        ];

        $payload =[
            'type' => "init_scoreboard",
            'data' => $data,
            'auth' => $this->auth_gen($data)
        ];

        $server->push($fd, json_encode($payload));
    }

	private function auth_gen($data) {
		return hash('sha256', $this->SECRET . json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
	}

	private function api_auth_gen($data) {
		return hash('sha256', $this->API_KEY . json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
	}

	protected function message_routing($data, $fd, $server) {
        $routing = $this->ROUTES[$data['message_type']];
        $loaded_class = $routing['class'];
        $method = $routing['method'];
        $handler = new $loaded_class($this->SECRET);
        $handler->$method($data, $fd, $server);
	}

	protected function get_user_api_key($user_id) {
		$url = $this->API_PROTOCOL . '://' . $this->API_ADDRESS . '/auth_check';
		$data = [
			"user_id" => $user_id,
		];

		$payload =[
			"user_id" => 0,
			'api_version' => "25.07.19",
			'action' => "socket_user_api_key_request",
			'data' => $data,
			'auth' => $this->api_auth_gen($data),
		];

		$out_payload = json_encode($payload);

		$options = [
			$this->API_PROTOCOL => [
				'method' => 'POST',
				'header' => "Content-Type: application/x-www-form-urlencoded\r\n" . "Content-length: " . strlen($out_payload) . "\r\n",
				'content' => $out_payload
			]
		];
		$context = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		return json_decode($result, true);
	}

	private function add_connection($fd, $token, $db) {
		$query = "INSERT INTO Connections (FD, token) VALUES (:fd, :token)";
		$vals_array = [
			[
				"name" => ":fd",
				"value" => $fd,
				"type" => "i"
			],
			[
				"name" => ":token",
				"value" => $token,
				"type" => "s"
			]
		];

		$db->make_query("insert", $query, $vals_array);
		return true;
	}

	protected function remove_connection($fd) {
		$db = new Utils\Sqlite_Handler();

		$query = "DELETE FROM Connections WHERE FD = :fd";
		$vals_array = [
			[
				"name" => ":fd",
				"value" => $fd,
				"type" => "i"
			]
		];

		$db->make_query("delete", $query, $vals_array);
		$db = null;
	}

	protected function initilization() {
		$db = new Utils\Sqlite_Handler();

		$query = "DELETE FROM Connections";
		$db->make_query("delete", $query, false);
		$query = "DELETE FROM random_str_store";
		$db->make_query("delete", $query, false);
        $query = "DELETE FROM scoreboard_connection";
        $db->make_query("delete", $query, false);
		$db = null;

		echo "Database Cleaned Up...\n";
	}

    private function init_routes() {
        include_once("routes/Request_Routes.php");
        $request_routes = new Request_Routes();
        $this->ROUTES = $request_routes->REQUEST_ROUTES;
    }

	public function init($vals) {
		$server = new Web_Sock($vals);
		$this->ascii_out();
        var_dump($this->ROUTES);
		$server->start();
	}
}