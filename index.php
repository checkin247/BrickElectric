<?php

require 'vendor/autoload.php';
require 'BrickRelayHttp.php';



// testing
$ip = '10.0.1.100';
$port = 80;
$password = '123456';
$relay = new Relay($ip, $port, $password);

$ch = '01';

// $command = 'K01=0';
// $command = 'K01=1';


// $response = $relay->send($command);
// $response = $relay->toggle($ch);
// $response = $relay->turnOn($ch);
// $response = $relay->turnOff($ch);
// $response = $relay->switch($ch);
// $response = $relay->getSerialNumber();
// $response = $relay->getRelayType();
// $response = $relay->getFirmwareVersion();
$response = $relay->getInfo();
// $response = $relay->setPassword('abcdef');
// $response = $relay->restoreDefaultPassword('abcdef');
// $response = $relay->setNetworkConfiguration('10.0.1.100', '10.0.1.1', '255.255.255.0');
// $response = $relay->relay;
var_dump($response);
// echo $response;