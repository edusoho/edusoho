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
    'CourseMembership',
    'LessonLiveTickets',
    'LessonLiveTicket',
    'Exercise',
    'ExerciseResult',
    'MeChatroomes',
    'MeCourses',
    'User',
    'Users',
    'Course',
    'Courses',
    'Lesson',
    'Lessons',
    'Thread',
    'Threads',
    'ChaosThreads',
    'ChaosThreadsPosts',
    'Apps',
    'App',
    'Analysis',
    'Homework',
    'HomeworkResult',
    'HomeworkManager',
    'ThreadManager',
    'Thread',
    'Upload',
    'Status'
);

foreach ($resources as $res) {
    $app["res.{$res}"] = $app->share(function () use ($res) {
        $class = "Topxia\\Api\\Resource\\{$res}";
        return new $class();
    }

    );
}
