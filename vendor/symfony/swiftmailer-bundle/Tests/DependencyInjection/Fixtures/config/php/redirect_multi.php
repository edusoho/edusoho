<?php
$container->loadFromExtension('swiftmailer', array(
    'delivery_addresses' => array('first@host.com', 'second@host.com')
));
