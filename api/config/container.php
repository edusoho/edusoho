<?php

$resources = array(
    'Article',
    'ArticleCategories',
    'Articles',
    'Classroom',
    'ClassroomMember',
    'ClassroomMembers',
    'Classrooms',
    'Exercise',
    'MeChatroomes',
    'User',
);

foreach ($resources as $res) {
    $app["res.{$res}"] = $app->share(function() use ($res) {
        $class = "Topxia\\Api\\Resource\\{$res}";
        return new $class();
    });
}
