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

	/**
	 * @var $relay
	 * an array which holds the information of the relay channels
	 */
	public $relay;
	
	function __construct($ip, $port = 80, $type = 'BEM104')
	{
		$this->ip = $ip;
		$this->port = $port;
		$this->connect();

		$this->constructRelay($type);
	}


	/**
	 * turn power on on $ch
	 * takes NC (normaly closed) / NO (normaly open) into consideration
	 *
	 * @param $ch
	 * the channel number of the relay
	 *
	 * @return string html response from server
	 */ 
	public function turnOn($ch)
	{
		$switch = 1;
		if( $this->relay[$ch]['connector'] == 'NO')
			$switch = 0;

		$command = 'K'.$ch.'='.$switch;
		return $this->send($command);
	}

	/**
	 * turn power off on $ch
	 * takes NC (normaly closed) / NO (normaly open) into consideration
	 *
	 * @param $ch
	 * the channel number of the relay
	 *
	 * @return string html response from server
	 */ 
	public function turnOff($ch)
	{
		$switch = 0;
		if( $this->relay[$ch]['connector'] == 'NO')
			$switch = 1;

		$command = 'K'.$ch.'='.$switch;
		return $this->send($command);
	}

	/**
	 * toggle the relay state
	 *
	 * @param $ch
	 * the channel number of the relay
	 *
	 * @return string html response from server
	 */
	public function toggle($ch)
	{
		$command = 'K'.$ch.'=2';
		return $this->send($command);
	}


	/**
	 * switch state
	 * off|->on->sleep->off or on|->off->sleep->on
	 *
	 * @param $ch
	 * the channel number of the relay
	 *
	 * @param int $timeout
	 * seconds to sleep between switching state
	 *
	 * @return string html response from server
	 */ 
	public function switch($ch, int $timeout = 3)
	{
		// use relay timer if we need more time
		// rather than using php execution time
		if( $timeout > 20 )
			$timeout = 3;

		$response = [];
		$command = 'K'.$ch.'=2';
		$response[] = $this->send($command);

		sleep($timeout);

		$response[] = $this->send($command);
		return $response;
	}

	/**
	 * set the lan adapter to this new configuration
	 *
	 * @param string $ip
	 * the new ip address for the lan adapter
	 *
	 * @param string $gateway
	 * the gateway ip address
	 *
	 * @param string $netmaks
	 * the netmask address
	 *
	 * @return string html response from server
	 */
	public function setNetworkConfiguration($ip, $gateway, $netmask)
	{
		$command = 'dhcp=0';
		$command .= '&ipaddr='.$ip;
		$command .= '&gateway='.$gateway;
		$command .= '&netmask='.$netmask;
		$command .= '&save=1&reboot=1';
		return $this->send($command);
	}

	/**
	 * turn DHCP mode on or off
	 *
	 * @param boolean $mode
	 * true to enable or false to disable DHCP mode
	 *
	 * @return string html response from server
	 */
	public function setDHCP($mode)
	{
		if( $mode )
			$command = 'dhcp=1';
		else
			$command = 'dhcp=0';
		
		$command .= '&save=1&reboot=1';
		return $this->send($command);
	}

	/**
	 * reboot the relay board
	 *
	 * @return string html response from server
	 */
	public function reboot()
	{
		$command = 'reboot=1';
		return $this->send($command);
	}

	/**
	 * configure the relay channels of the relay board
	 * each channel is NO / normally open or NC / normally closed
	 * the manufacturers documentation is considering "NC", while
	 * the relays behave the opposite when using "NO".
	 * setting up the connector type improves the logic (on/off).
	 * the master should now the connection type
	 *
	 * @param string $type
	 * the board type, important to know how many relays are connected
	 */
	private function constructRelay($type)
	{
		switch ($type) {
			case 'BEM104':
			default:
				$relays = 2;
				break;
		}

		for($r = 1; $r <= $relays; $r++)
		{
			if( strlen($relays) == 1)
				$r = '0'.$r;

			$this->setRelayConnector($r, 'NO');
		}
	}

	/**
	 * set the connector type of a relay channel ($ch)
	 *
	 * @param int $ch
	 * the channel of the relay board to configure
	 *
	 * @param string $connector
	 * "NC" for normaly closed or "NO" for normaly open
	 */
	private function setRelayConnector($ch, $connector)
	{
		if( $connector != 'NO' || $connector != 'NC')
			$connector = 'NO';

		$this->relay[$ch]['connector'] = $connector;
	}

	/**
	 * test the connection to the board and create
	 * client on success
	 */
	private function connect()
	{
		$conn = @fsockopen($this->ip, $this->port, $errno, $errstr, 1);
	    if ( ! $conn) {
	    	return;
	    }
	        
	    $this->status = true;
	    $this->client = new Client();
	}

	/**
	 * send the command to the relay board
	 * if $this->status is false, (see connect())
	 * the command is not sent
	 *
	 * @param string $command
	 * the command to send to the relay board
	 */
	public function send($command)
	{
		if( ! $this->status )
			return false;

		$response = $this->client->request('GET', 'http://'.$this->ip.':'.$this->port.'/'.$command);
		return $response->getBody();
	}
}