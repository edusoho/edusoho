<?php

$container->loadFromExtension('swiftmailer', array(
    'default_mailer' => 'mailer_on',
    'mailers' => array(
        'mailer_on' => null,
        'mailer_off' => array('disable_delivery' => true),
    ),
));
