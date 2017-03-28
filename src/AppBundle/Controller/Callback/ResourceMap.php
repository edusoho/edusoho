<?php

namespace AppBundle\Controller\Callback;

class ResourceMap
{
    public static function getClass($resource)
    {
        $namespace = __NAMESPACE__;
        $classMap = array(
            'cloud_search_courses' => $namespace.'\\CloudSearch\\Resource\\Courses',
            'cloud_search_course' => $namespace.'\\CloudSearch\\Resource\\Course',
            'cloud_search_lessons' => $namespace.'\\CloudSearch\\Resource\\Lessons',
            'cloud_search_lesson' => $namespace.'\\CloudSearch\\Resource\\Lesson',
            'cloud_search_open_courses' => $namespace.'\\CloudSearch\\Resource\\OpenCourses',
            'cloud_search_open_course' => $namespace.'\\CloudSearch\\Resource\\OpenCourse',
            'cloud_search_open_course_lessons' => $namespace.'\\CloudSearch\\Resource\\OpenCourseLessons',
            'cloud_search_open_course_lesson' => $namespace.'\\CloudSearch\\Resource\\OpenCourseLesson',
            'cloud_search_lessons' => $namespace.'\\CloudSearch\\Resource\\Lessons',
            'cloud_search_lesson' => $namespace.'\\CloudSearch\\Resource\\Lesson',
            'cloud_search_articles' => $namespace.'\\CloudSearch\\Resource\\Articles',
            'cloud_search_article' => $namespace.'\\CloudSearch\\Resource\\Article',
            'cloud_search_users' => $namespace.'\\CloudSearch\\Resource\\Users',
            'cloud_search_user' => $namespace.'\\CloudSearch\\Resource\\User',
            'cloud_search_chaos_threads' => $namespace.'\\CloudSearch\\Resource\\ChaosThreads',
        );

        if (!isset($classMap[$resource])) {
            throw new \InvalidArgumentException(sprintf('resource not available: %s', $resource));
        }

        return $classMap[$resource];
    }
}
