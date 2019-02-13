<?php

/**
 * HTTP/TCP protocol implementation of the BrickElectronic Relay
 * Tested with BEM104
 */

use GuzzleHttp\Client;

class Relay
{
	/**
	 * @var $ip
	 * the ip of the relay
	 */
	private $ip;

	/**
	 * @var $port
	 * the port of the relay
	 */
	private $port;

	/**
	 * @var $status
	 * boolean, can connect to the relay
	 */
	public $status = false;

	/**
	 * @var $client
	 * client to use to crate the http reques
	 */
	private $client;
	
	function __construct($ip, $port = 80)
	{
		$this->ip = $ip;
		$this->port = $port;
		$this->connect();
	}

	private function connect()
	{
		$conn = @fsockopen($this->ip, $this->port, $errno, $errstr, 1);
	    if ( ! $conn) {
	    	return;
	    }
	        
	    $this->status = true;
	    $this->client = new Client();
	}

	public function send($command)
	{
		if( ! $this->status )
			return false;

		$response = $this->client->request('GET', 'http://'.$this->ip.':'.$this->port.'/'.$command);
		return $response->getBody();
	}
}