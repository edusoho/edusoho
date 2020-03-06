<?php

$container->loadFromExtension('swiftmailer', array(
    'delivery_addresses' => ['single@host.com'],
    'delivery_whitelist' => array('/foo@.*/'),
));
