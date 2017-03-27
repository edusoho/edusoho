<?php
$container->loadFromExtension('swiftmailer', array(
    'delivery_address'   => 'single@host.com',
    'delivery_whitelist' => array('/foo@.*/', '/.*@bar.com$/'),
));
