<?php

namespace AppBundle\Controller\Callback\CloudSearch;

use Codeages\Biz\Framework\Context\BizAware;
use AppBundle\Controller\Callback\ProcessorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class CloudSearchProcessor extends BizAware implements ProcessorInterface
{
    private $pool = array();

    public function getProviderClassMap($type)
    {
        $namespace = __NAMESPACE__;

        $classMap = array(
            'courses' => $namespace.'\\Resource\\Courses',
            'course' => $namespace.'\\Resource\\Course',
            'lessons' => $namespace.'\\Resource\\Lessons',
            'lesson' => $namespace.'\\Resource\\Lesson',
            'open_courses' => $namespace.'\\Resource\\OpenCourses',
            'open_course' => $namespace.'\\Resource\\OpenCourse',
            'open_course_lessons' => $namespace.'\\Resource\\OpenCourseLessons',
            'open_course_lesson' => $namespace.'\\Resource\\OpenCourseLesson',
            'articles' => $namespace.'\\Resource\\Articles',
            'article' => $namespace.'\\Resource\\Article',
            'users' => $namespace.'\\Resource\\Users',
            'user' => $namespace.'\\Resource\\User',
            'chaos_threads' => $namespace.'\\Resource\\ChaosThreads',
            'classroom' => $namespace.'\\Resource\\Classroom',
            'classrooms' => $namespace.'\\Resource\\Classrooms',
        );

        if (!isset($classMap[$type])) {
            throw new \InvalidArgumentException(sprintf('Provider not available: %s', $type));
        }

        return $classMap[$type];
    }

    /**
     * @param [type] $type
     *
     * @return \AppBundle\Controller\Callback\CloudSearch\BaseProvider
     */
    public function getProvider($type)
    {
        if (empty($this->pool[$type])) {
            $class = $this->getProviderClassMap($type);
            $instance = new $class();
            $instance->setBiz($this->biz);
            $this->pool[$type] = $instance;
        }

        return $this->pool[$type];
    }

    public function execute(Request $request)
    {
        $method = strtolower($request->getMethod());
        if ('get' != $method) {
            throw new \InvalidArgumentException(sprintf('unsupported method: %s', $method));
        }

        $providerType = $request->query->get('provider');
        $provider = $this->getProvider($providerType);
        $token = $request->headers->get('X-Auth-Token');
        $provider->checkToken($token);

        return new JsonResponse($provider->get($request));
    }
}
