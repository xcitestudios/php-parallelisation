<?php

/*
If you want to override the settings below create a file with the following code called config.local.php:
<?php

return [
    'amqp_worker_host'       => "localhost",
    'amqp_worker_port'       => 5672,
    'amqp_worker_username'   => "guest",
    'amqp_worker_password'   => "guest",
    'amqp_worker_vhost'      => "/",
    'amqp_worker_ssl'        => false,
    'amqp_worker_queue_name' => "rpc.queue.data.conversion.csv-to-json",
    'ssl_cert_path'          => "/etc/ssl/certs"
    'amqp_publisher_host'     => "localhost",
    'amqp_publisher_port'     => 5672,
    'amqp_publisher_username' => "guest",
    'amqp_publisher_password' => "guest",
    'amqp_publisher_vhost'    => "/",
    'amqp_publisher_ssl'      => false,
    'amqp_publish_exchange'   => 'data_gateway',
    'amqp_publish_key'        => 'data.conversion.csv-to-json',
];
*/

$config = [
    'amqp_worker_host'       => "localhost",
    'amqp_worker_port'       => 5672,
    'amqp_worker_username'   => "guest",
    'amqp_worker_password'   => "guest",
    'amqp_worker_vhost'      => "/",
    'amqp_worker_ssl'        => false,
    'amqp_worker_queue_name' => "rpc.queue.data.conversion.csv-to-json",
    'ssl_cert_path'          => "/etc/ssl/certs",
    'amqp_publisher_host'     => "localhost",
    'amqp_publisher_port'     => 5672,
    'amqp_publisher_username' => "guest",
    'amqp_publisher_password' => "guest",
    'amqp_publisher_vhost'    => "/",
    'amqp_publisher_ssl'      => false,
    'amqp_publish_exchange'   => 'data_gateway',
    'amqp_publish_key'        => 'data.conversion.csv-to-json',
];

if (file_exists(__DIR__ . '/config.local.php') && is_readable(__DIR__ . '/config.local.php')) {
    $localConfig = include 'config.local.php';

    $config = array_merge($config, $localConfig);
}

return $config;