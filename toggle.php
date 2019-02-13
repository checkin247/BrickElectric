<?php

/**
 * Toggle BEM104 relay board 
 * configure the relay and address
 */

const RELEY_IP = '10.0.1.100';
const RELAY_CH = '01';
const PROTO = 'http://';
const TIMEOUT = 3;

/**
 * turn relay off and back on 
 * requires output beeing NC 
 */


// turn off
$off = file_get_contents(PROTO.RELEY_IP.'/K'.RELAY_CH.'=1');

// wait - keep it turned of
sleep(TIMEOUT);

// turn on 
$on = file_get_contents(PROTO.RELEY_IP.'/K'.RELAY_CH.'=0');

echo $off;
echo "<br>";
echo $on;