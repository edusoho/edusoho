<?php

$container->loadFromExtension('swiftmailer', array(
    'default_mailer' => 'failover',
    'mailers' => array(
        'failover' => null,
    ),
));
