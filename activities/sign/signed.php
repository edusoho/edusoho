<?php

$nickname = $_GET['nickname'];

$user = $GLOBALS['_API']->getUser($nickname);

$GLOBALS['_DB']->insert('user_sign', array(
    'user_id' => $user['id'],
    'course_id' => 158,
    'nickname' => $nickname,
    'created_time' => time(),
));


echo '签到成功';
exit;
