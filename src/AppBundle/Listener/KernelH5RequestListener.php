<?php

namespace AppBundle\Listener;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Common\DeviceToolkit;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class KernelH5RequestListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (HttpKernelInterface::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }

        if ('GET' !== $request->getMethod()) {
            return;
        }

        $pathInfo = $request->getPathInfo();

        try {
            $route = $this->container
                ->get('router')
                ->getMatcher()
                ->match($pathInfo);
        } catch (ResourceNotFoundException $ne) {
            return;
        }

        $h5 = empty($route['_h5']) ? array() : $route['_h5'];
        if (empty($h5)) {
            return;
        }

        $wapSetting = $this->getSettingService()->get('wap');
        if (empty($wapSetting['version']) || 2 != $wapSetting['version']) {
            return;
        }

        if (!DeviceToolkit::isMobileClient()) {
            return;
        }
        $url = $this->transfer($route, $pathInfo, $request);
        $response = new RedirectResponse($url);
        $event->setResponse($response);
    }

    protected function transfer($route, $pathInfo, $request)
    {
        $params = $request->query->all();
        $query = http_build_query($params);
        if (in_array($route['_route'], array('my_course_show', 'course_show'))) {
            $pathInfo = $this->container->get('router')->generate('course_show', array('id' => $route['id']), UrlGeneratorInterface::ABSOLUTE_PATH);
            if (isset($params['loginToken'])) {
                $pathInfo = $pathInfo.'/loginToken/'.$params['loginToken'];
            }
        }

        if (in_array($route['_route'], array('classroom_reviews', 'classroom_introductions'))) {
            $pathInfo = $this->container->get('router')->generate('classroom_show', array('id' => $route['id']), UrlGeneratorInterface::ABSOLUTE_PATH);
        }

        if ('course_set_explore' == $route['_route']) {
            $query = array();
            $pathInfo = $this->container->get('router')->generate('course_set_explore', array(), UrlGeneratorInterface::ABSOLUTE_PATH);
        }
        if ('task_live_entry' == $route['_route']) {
            $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($route['courseId'], $route['activityId']);
            $params = array(
                'courseId' => $route['courseId'],
                'taskId' => $task['id'],
                'type' => $task['type'],
                'title' => $task['title'],
            );
            $query = http_build_query($params);
            $pathInfo = '/live';
        }

        return empty($query) ? '/h5/index.html#'.$pathInfo : '/h5/index.html#'.$pathInfo.'?'.$query;
    }

    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    protected function trans($id, array $parameters = array())
    {
        return $this->container->get('translator')->trans($id, $parameters);
    }

    protected function getBiz()
    {
        return $this->container->get('biz');
    }
}
