<?php

$resources = array(
    'Article',
    'ArticleCategories',
    'Articles',
    'Classroom',
    'ClassroomMember',
    'ClassroomMembers',
    'Classrooms',
    'MeChatroomes',
    'User',
    'Users',
    'Course',
    'Courses',
    'CourseLesson',
    'CourseLessons',
    'Thread',
    'Threads',
    'Apps',
    'App'
);

foreach ($resources as $res) {
    $app["res.{$res}"] = $app->share(function() use ($res) {
        $class = "Topxia\\Api\\Resource\\{$res}";
        return new $class();
    });
}
