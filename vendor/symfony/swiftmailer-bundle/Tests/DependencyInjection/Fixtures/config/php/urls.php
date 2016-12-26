<?php
$container->loadFromExtension('swiftmailer', array(
    'default_mailer' => 'smtp_mailer',
    'mailers' => array(
        'smtp_mailer' => array(
            'url' => 'smtp://username:password@example.com:12345?encryption=tls&auth_mode=login',
        ),
    ),
));
