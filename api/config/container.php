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
    'Users',
    'Course',
    'Courses',
    'Lesson',
    'Lessons',
    'Thread',
    'Threads',
    'ChaosThreads',
    'Apps',
    'App',
    'Homework',
    'HomeworkResult',
    'Upload'
);

foreach ($resources as $res) {
    $app["res.{$res}"] = $app->share(function() use ($res) {
        $class = "Topxia\\Api\\Resource\\{$res}";
        return new $class();
    });
}
