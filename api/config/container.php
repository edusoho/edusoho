<?php

$resources = array(
    'Article',
    'ArticleCategories',
    'Articles',
    'Classroom',
    'ClassroomMember',
    'ClassroomMembers',
    'Classrooms',
    'CourseMember',
    'CourseMembers',
    'Exercise',
    'ExerciseResult',
    'MeChatroomes',
    'User',
    'Apps',
    'App',
    'Analysis',
    'Homework',
    'HomeworkResult',
    'HomeworkManager',
    'ThreadManager',
    'Upload'
);

foreach ($resources as $res) {
    $app["res.{$res}"] = $app->share(function() use ($res) {
        $class = "Topxia\\Api\\Resource\\{$res}";
        return new $class();
    });
}
