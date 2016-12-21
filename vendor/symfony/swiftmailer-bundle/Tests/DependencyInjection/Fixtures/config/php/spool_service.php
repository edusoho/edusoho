<?php
$container->loadFromExtension('swiftmailer', array(
    'spool' => array('type' => 'service', 'id' => 'custom_service_id')
));
