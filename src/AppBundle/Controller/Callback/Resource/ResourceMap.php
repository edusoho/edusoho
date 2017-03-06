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
        );

        if (!isset($classMap[$resource])) {
            throw new \InvalidArgumentException(sprintf('resource not available: %s', $resource));
        }

        return $classMap[$resource];
    }
}