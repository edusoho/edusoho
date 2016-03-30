<?php

$resources = array(
    'Article',
    'ArticleCategories',
    'Articles',
    'Classroom',
    'ClassroomMember',
    'ClassroomMembers',
    'Classrooms',
    'ClassroomStatus',
    'CourseMember',
    'CourseMembers',
    'CourseMembership',
    'CourseNotes',
    'CourseReviews',
    'LessonLiveTickets',
    'LessonLiveTicket',
    'LessonReplay',
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
    'Status',
    'DiscoveryColumn',
    'Playlist'
);

foreach ($resources as $res) {
    $app["res.{$res}"] = $app->share(function () use ($res) {
        $class = "Topxia\\Api\\Resource\\{$res}";
        return new $class();
    });
}
