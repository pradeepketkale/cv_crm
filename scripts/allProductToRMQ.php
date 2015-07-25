<?php

require_once __DIR__ . '/../lib/amqplib/vendor/autoload.php';
require_once __DIR__ . '/dbConnection.php';
require_once __DIR__ . '/rabbitConfig.php';

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPConnection(RABBIT_HOST, RABBIT_PORT, RABBIT_USERNAME, RABBIT_PASSWORD);
$channel = $connection->channel();

$productIdQuery = mysql_query("SELECT `entity_id` FROM `catalog_product_entity`",$mainConnection);  

	while($productIdResult = mysql_fetch_array($productIdQuery)){	

		$msg = new AMQPMessage($productIdResult['entity_id'],array('delivery_mode' => 2));

		$channel->basic_publish($msg, $argv[1]);//$argv[1] the name of queue

		echo " [x] Sent ", $productIdResult['entity_id'], "\n";
	}
$channel->close();
$connection->close();

?>
