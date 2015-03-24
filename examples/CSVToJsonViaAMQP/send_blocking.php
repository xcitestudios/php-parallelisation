<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../../vendor/autoload.php';

$systemConfig = include 'config.dist.php';

$config = new \com\xcitestudios\Network\Server\Configuration\AMQPServerConfiguration();
$config->setHost($systemConfig['amqp_publisher_host'])
       ->setPort($systemConfig['amqp_publisher_port'])
       ->setUsername($systemConfig['amqp_publisher_username'])
       ->setPassword($systemConfig['amqp_publisher_password'])
       ->setVHost($systemConfig['amqp_publisher_vhost'])
       ->setSSL($systemConfig['amqp_publisher_ssl']);

$conn = \com\xcitestudios\Network\Server\Connection\AMQPConnection::createConnectionUsingPHPAMQPLib($config, $systemConfig['ssl_cert_path']);

$dispatcher = new \com\xcitestudios\Parallelisation\Distributed\Queue\AMQP\RPCDispatcher($conn, null);
$dispatcher->setDefaultExchange($systemConfig['amqp_publish_exchange']);
$dispatcher->setDefaultRoutingKey($systemConfig['amqp_publish_key']);
$dispatcher->start();


$handler = new \com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJson\AMQP\BlockingCSVToJsonFileDispatcher(
    $dispatcher, 100
);

$handler->setFilename(__DIR__ . '/sample.csv');
$result = $handler->process();

print_r(json_decode($result));