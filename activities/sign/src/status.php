<?php

$courseId = $_GET['courseId'];

$statement = $GLOBALS['_DB']->executeQuery('select * from user_sign', array(
    'course_id' => $courseId,
));

$result = $statement->fetchAll();

echo json_encode($result);
exit;
