<?php

require 'vendor/autoload.php';
require 'BrickRelayHttp.php';



// testing
$ip = '10.0.1.100';
$port = 80;
$relay = new Relay($ip, $port);

$command = 'K01=0';
// $command = 'K01=1';


$response = $relay->send($command);
echo $response;