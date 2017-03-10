<?php

namespace AppBundle\Controller\Callback\Resource;

class ResourceMap
{
    public static function getClass($resource)
    {
        $namespace = __NAMESPACE__;
        $classMap = array(
            'cloud_search_courses' => $namespace.'\\CloudSearch\\Courses',
            'cloud_search_course' => $namespace.'\\CloudSearch\\Course',
            'cloud_search_lessons' => $namespace.'\\CloudSearch\\Lessons',
            'cloud_search_lesson' => $namespace.'\\CloudSearch\\Lesson',
            'cloud_search_open_courses' => $namespace.'\\CloudSearch\\OpenCourses',
            'cloud_search_open_course' => $namespace.'\\CloudSearch\\OpenCourse',
            'cloud_search_open_course_lessons' => $namespace.'\\CloudSearch\\OpenCourseLessons',
            'cloud_search_open_course_lesson' => $namespace.'\\CloudSearch\\OpenCourseLesson',
            'cloud_search_lessons' => $namespace.'\\CloudSearch\\Lessons',
            'cloud_search_lesson' => $namespace.'\\CloudSearch\\Lesson',
            'cloud_search_articles' => $namespace.'\\CloudSearch\\Articles',
            'cloud_search_article' => $namespace.'\\CloudSearch\\Article',
            'cloud_search_users' => $namespace.'\\CloudSearch\\Users',
            'cloud_search_user' => $namespace.'\\CloudSearch\\User',
            'cloud_search_chaos_threads' => $namespace.'\\CloudSearch\\ChaosThreads',
        );

        if (!isset($classMap[$resource])) {
            throw new \InvalidArgumentException(sprintf('resource not available: %s', $resource));
        }

        return $classMap[$resource];
    }
}
