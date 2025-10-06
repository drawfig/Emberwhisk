<?php

use Handlers\connection_handler;

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
    if(file_exists(__DIR__ . "/Middleware/" . str_replace("Middleware\\", "", $class_name) . ".php")) {
        require_once (__DIR__ . "/Middleware/" . str_replace("Middleware\\", "", $class_name) . ".php");
    }
});

spl_autoload_register(function ($class_name) {
	include ($class_name . ".php");
});

class Core {
    public $RUN_TYPE;
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
	public $SECRET;

    public $API_AUTH_ADDRESS;

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
		$this->API_AUTH_ADDRESS = $EnvBoot->get_var("api_auth_address");
		$this->SECRET = $EnvBoot->get_var("secret");
        $this->RUN_TYPE = $EnvBoot->get_var("run_type");

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

    public function sqlite3_check() {
        $out = system("php -m | grep sqlite3");

        if($out == "") {
            return false;
        }
        else {
            return true;
        }
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
                                     |                                 v0.0.2-alpha2                                |
                                     |                                 Bewitched Fox                                |
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
            $logger = new Utils\Logging_system();
            $logger->log("Connection Rejected to {$fd}", $data['user_id'], "Handshake Error");;
			echo "Rejected Connection to {$fd} \n";
		}
		$db = null;
	}

	private function auth_gen($data) {
		return hash('sha256', $this->SECRET . json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
	}

	private function api_auth_gen($data) {
		return hash('sha256', $this->API_KEY . json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
	}

	protected function message_routing($data, $fd, $server) {
        switch ($data['message_type']) {
            case "init_handshake":
                $this->send_handshake($server, $fd);
                break;
            case "handshake":
                $this->handle_handshake_resp($data['data'], $fd);
                break;
            default:
                $this->handle_normal_routing($data, $fd, $server);
        }
	}

    private function run_middleware($data, $fd, $server) {
        $grouping = new Middleware\Middleware_Manager();
        $run_chk = $grouping->run($data, $fd, $server, $this->RUN_TYPE);

        if($run_chk === true) {
            return true;
        }
        else {
            return false;
        }
    }

    private function handle_normal_routing($data, $fd, $server) {
        if(array_key_exists($data['message_type'], $this->ROUTES)) {
            $routing = $this->ROUTES[$data['message_type']];
            $middleware_resp = $this->run_middleware($data, $fd, $server);
            $db = new Utils\Sqlite_Handler();
            if($middleware_resp) {
                if ($routing['protected']) {
                    $auth = new Utils\Authentication_System();
                    $auth->authenticate($fd, $data['user_id'], $data['auth'], $data['data'], $server, $db);
                }
                include_once("Handlers/" . $routing['class'] . ".php");
                $loaded_class = $routing['class'];
                $method = $routing['method'];
                $handler = new $loaded_class($this->SECRET, $data, $fd, $server, $db, $this->RUN_TYPE);
                $handler->$method();
            }
            else {
                include_once("Handlers/middleware_rejection_handler.php");
                $handler = new middleware_rejection_handler($this->SECRET, $data, $fd, $server, $db, $this->RUN_TYPE);
                $handler->run();
            }
        }
        $db = null;
    }

    protected function on_connection($fd, $server) {
        $db = new Utils\Sqlite_Handler();
        include_once("Handlers/connection_handler.php");
        $handler = new connection_handler($this->SECRET,[], $fd, $server, $db, $this->RUN_TYPE);
        $handler->run();
        $db = null;
    }

    protected function on_disconnect($fd, $server) {
        $db = new Utils\Sqlite_Handler();
        include_once("Handlers/disconnect_handler.php");
        $handler = new disconnect_handler($this->SECRET,[], $fd, $server, $db, $this->RUN_TYPE);
        $handler->run();
        $db = null;
    }

	protected function get_user_api_key($user_id) {
		$url = $this->API_PROTOCOL . '://' . $this->API_ADDRESS . '/' . $this->API_AUTH_ADDRESS;
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

	protected function initilization() {
		$db = new Utils\Sqlite_Handler();

		$query = "DELETE FROM Connections";
		$db->make_query("delete", $query, false);
		$query = "DELETE FROM random_str_store";
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
        if($this->sqlite3_check()) {
            $server = new Web_Sock($vals);
            $this->ascii_out();
            $server->start();
        }
        else {
            print("\033[31m============================================================================ \n");
            print("\033[31mSQLite3 php module is missing and is required to run the Emberwhisk server.\n");
            print("\033[31m============================================================================ \n");
            print("\033[0m");
        }
	}
}