<?php

use AppBundle\Component\Activity\ActivityRuntimeContainerV1;

$container = ActivityRuntimeContainerV1::instance();

$activityProxy = $container->activityProxy;

$context = $activityProxy->activityContext;

return $activityProxy->render('create_or_update.html.twig', array(
    'activity' => $activityProxy,
    'drfa'
));