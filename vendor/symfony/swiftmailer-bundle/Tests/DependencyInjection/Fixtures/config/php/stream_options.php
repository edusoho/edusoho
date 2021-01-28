<?php
$container->loadFromExtension('swiftmailer', array(
    'transport' => 'smtp',
    'host' => 'example.org',
    'port' => '12345',
    'source_ip' => '127.0.0.1',
    'stream_options' => array(
        'ssl' => array(
            'verify_peer' => true,
            'verify_depth' => 5,
            'cafile' => '/etc/ssl/cacert.pem',
            'CN_match' => 'ssl.example.com',
        ),
    ),
));
