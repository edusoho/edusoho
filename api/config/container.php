<?php

$resources = array(
    'Article',
    'ArticleCategories',
    'Articles',
    'Classroom',
    'ClassroomMembers',
    'Classroom/Member',
    'Classroom/Members',
    'Classrooms',
    'ClassroomStatus',
    'ClassroomStatuses',
    'Course/Member',
    'Course/Members',
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
    'Playlist',
    'MeIMLogin',
    'MeIMConversation',
    'CourseThread',
    'CourseThreads',
    'CourseThreadPost',
    'CourseThreadPosts',
    'ClassRoomPlay',
    'ClassRoomPlayStatus',
    'ClassRoomThread',
    'ClassRoomThreads',
    'ThreadPosts',
    'ThreadPost'
);

foreach ($resources as $res) {
    $app["res.{$res}"] = $app->share(function () use ($res) {
        $class = "Topxia\\Api\\Resource";
        $segments = explode('/', $res);
        foreach ($segments as $seg) {
            $class .= "\\{$seg}";
        }

        return new $class();
    });
}
