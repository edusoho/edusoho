<?php

$container->loadFromExtension('swiftmailer', array(
    'default_mailer' => 'secondary_mailer',
    'mailers' => array(
        'first_mailer' => array(
            'transport' => 'smtp',
            'username' => 'user_first',
            'password' => 'pass_first',
            'host' => 'example.org',
            'port' => '12345',
            'encryption' => 'tls',
            'auth-mode' => 'login',
            'timeout' => '1000',
            'source_ip' => '127.0.0.1',
            'local_domain' => 'first.example.org',
            'logging' => true,
            'sender_address' => 'first-sender@example.org',
            'delivery_address' => 'first@example.org',
            'delivery_whitelist' => array(
                '/firstfoo@.*/',
                '/.*@firstbar.com$/',
            ),
        ),
        'secondary_mailer' => array(
            'transport' => 'smtp',
            'username' => 'user_secondary',
            'password' => 'pass_secondary',
            'host' => 'example.org',
            'port' => '54321',
            'encryption' => 'tls',
            'auth-mode' => 'login',
            'timeout' => '1000',
            'source_ip' => '127.0.0.1',
            'local_domain' => 'second.example.org',
            'logging' => true,
            'spool' => array(
                'type' => 'memory',
                ),
            'delivery_address' => 'secondary@example.org',
            'delivery_whitelist' => array(
                '/secondaryfoo@.*/',
                '/.*@secondarybar.com$/',
            ),
        ),
        'third_mailer' => array(
            'transport' => 'smtp',
            'username' => 'user_third',
            'password' => 'pass_third',
            'host' => 'example.org',
            'port' => '12345',
            'encryption' => 'tls',
            'auth-mode' => 'login',
            'timeout' => '1000',
            'source_ip' => '127.0.0.1',
            'local_domain' => 'third.example.org',
            'logging' => true,
            'spool' => array(
                'type' => 'file',
                ),
            'sender_address' => 'third-sender@example.org',
            'delivery_address' => 'third@example.org',
            'delivery_whitelist' => array(
                '/thirdfoo@.*/',
                '/.*@thirdbar.com$/',
            ),
        ),
    ),
));
