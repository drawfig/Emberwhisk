<?php

use OpenSwoole\WebSocket\{Frame, Server};
use OpenSwoole\Constant;
use OpenSwoole\Http\Request;


class Web_Sock extends Core
{
	public function start() {
		$ssl_options = [
			'ssl_cert_file' => $this->SSL_CERT,
			'ssl_key_file' => $this->SSL_KEY,
			'ssl_verify_peer' => $this->convertBool($this->SSL_VERIFY_PEER),
			'ssl_allow_self_signed' => $this->convertBool($this->SSL_ALLOW_SELF_SIGNED),
		];

		echo "Starting Web Sock...\n";
		$this->initilization();

		$server = new Server($this->ADDRESS, $this->PORT, Server::POOL_MODE, Constant::SOCK_TCP);

		if($this->ENVIRONMENT == 'Testing' || $this->ENVIRONMENT == 'Production') {
			$server->set($ssl_options);
		}

		$server->set([
			'worker_num' => intval($this->WORKER_COUNT),
			'daemonize' => $this->convertBool($this->DAEMONIZATION)
		]);

		echo "Web Sock started on {$this->PROTOCOL}://{$this->ADDRESS}:{$this->PORT}\n";
        $logger = new Utils\Logging_system();
        $logger->log("Server Started", null, "system status");

		$server->on('Open', function ($server, $frame) {
			$fd = $frame->fd;
			$this->on_connection($fd, $server);
		});

		$server->on('Message', function ($server, $frame) {
			$this->message_routing(json_decode($frame->data, true), $frame->fd, $server);
		});

		$server->on('Close', function ($server, $fd) {
			$this->remove_connection($fd);
			echo "Connection closed: {$fd}\n";
		});

		$server->start();
	}
}