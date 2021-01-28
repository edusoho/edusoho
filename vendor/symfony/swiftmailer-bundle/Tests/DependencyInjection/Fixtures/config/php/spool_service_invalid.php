<?php

$container->loadFromExtension('swiftmailer', array(
    'spool' => array('type' => 'service'),
));
