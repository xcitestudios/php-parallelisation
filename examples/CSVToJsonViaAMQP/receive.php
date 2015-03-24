<?php

require __DIR__ . '/../../vendor/autoload.php';

$systemConfig = include 'config.dist.php';

$config = new \com\xcitestudios\Network\Server\Configuration\AMQPServerConfiguration();
$config->setHost($systemConfig['amqp_worker_host'])
    ->setPort($systemConfig['amqp_worker_port'])
    ->setUsername($systemConfig['amqp_worker_username'])
    ->setPassword($systemConfig['amqp_worker_password'])
    ->setVHost($systemConfig['amqp_worker_vhost'])
    ->setSSL($systemConfig['amqp_worker_ssl']);

$eventClass = function($body, $routingKey){
    $json = json_decode($body);
    if ($json->type === 'com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJson') {
        return \com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJson\Event::class;
    }

    return null;
};

$handler = function($body, $routingKey) {
    return new \com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJson\AMQP\CSVToJsonWorker();
};

$conn = \com\xcitestudios\Network\Server\Connection\AMQPConnection::createConnectionUsingPHPAMQPLib($config, $systemConfig['ssl_cert_path']);


$worker = new \com\xcitestudios\Parallelisation\Distributed\Queue\AMQP\RPCWorker($conn, $systemConfig['amqp_worker_queue_name'], $handler, $eventClass);


$worker->start();