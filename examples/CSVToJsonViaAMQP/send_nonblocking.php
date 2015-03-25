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

$storage = new \com\xcitestudios\Generic\Data\KeyValueStorage\CompressedArrayStore();

$conn = \com\xcitestudios\Network\Server\Connection\AMQPConnection::createConnectionUsingPHPAMQPLib($config, $systemConfig['ssl_cert_path']);


$dispatcher = new \com\xcitestudios\Parallelisation\Distributed\Queue\AMQP\RPCDispatcher($conn, null, 0, null, null, $storage);
$dispatcher->setDefaultExchange($systemConfig['amqp_publish_exchange']);
$dispatcher->setDefaultRoutingKey($systemConfig['amqp_publish_key']);
$dispatcher->start();

$handler = new \com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJson\AMQP\CSVToJsonFileDispatcher(
    $dispatcher, 10
);

$handler->addEventReturnedCallback(function(\com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJson\Event $event) use ($storage){
    $json = $event->getOutput()->getJsonObjectStrings();

    echo 'Got ', count($json), ' rows', "\n";
    echo '[' . implode(',', $json) . ']', "\n";
});

$handler->setFilename(__DIR__ . '/sample.csv');
$handler->process();

while (!$handler->isFinished()) {
    usleep(1000);
}