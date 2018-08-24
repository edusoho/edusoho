<?php

$container = \AppBundle\Component\Activity\ActivityRuntimeContainer::instance();

$activityProxy = $container->getActivityProxy();
$course = $activityProxy->getActivityContext()->getCourse();

$db = $container->getDB();
$statement = $db->executeQuery('select * from user_sign', array(
    'course_id' => $course['id'],
));

$result = $statement->fetchAll();

echo json_encode($result);
exit;
