<?php
require('library/Solarium/Autoloader.php');
Solarium_Autoloader::register();

// check solarium version available
echo 'Solarium library version: ' . Solarium_Version::VERSION . ' - ';

// create a client instance
$client = new Solarium_Client();

// create a ping query
$ping = $client->createPing();

// execute the ping query
try{
    $client->ping($ping);
    echo 'Ping query succesful';
}catch(Solarium_Exception $e){
    echo 'Ping query failed';
}


