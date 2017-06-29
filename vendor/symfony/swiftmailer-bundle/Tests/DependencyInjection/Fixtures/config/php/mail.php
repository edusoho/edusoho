<?php

$container->loadFromExtension('swiftmailer', array(
    'transport' => 'mail',
    'spool' => null,
));
