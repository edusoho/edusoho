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
    'Course/Note',
    'Course/Notes',
    'Course/Lesson',
    'Course/Lessons',
    'Course/LessonsToBeDelete',
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
    'IM/MeLogin',
    'IM/Conversations',
    'IM/MyConversation',
    'IM/MyConversations',
    'CourseThread',
    'CourseThreads',
    'CourseThreadPost',
    'CourseThreadPosts',
    'ClassRoomPlay',
    'ClassRoomPlayStatus',
    'ClassRoomThread',
    'ClassRoomThreads',
    'ThreadPosts',
    'ThreadPost',
    'OpenCourse',
    'OpenCourses',
    'OpenCourse/Lesson',
    'OpenCourse/Lessons'
);

foreach ($resources as $res) {
    $app["res.{$res}"] = $app->share(function () use ($res) {
        $class    = "Topxia\\Api\\Resource";
        $segments = explode('/', $res);
        foreach ($segments as $seg) {
            $class .= "\\{$seg}";
        }

        return new $class();
    });
}
