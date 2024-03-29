<?php

namespace AppBundle\Listener;

use AppBundle\Common\DeviceToolkit;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
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

        $h5 = empty($route['_h5']) ? [] : $route['_h5'];
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
        if (in_array($route['_route'], ['my_course_show', 'course_show'])) {
            $pathInfo = $this->container->get('router')->generate('course_show', ['id' => $route['id']], UrlGeneratorInterface::ABSOLUTE_PATH);
        }
        if (isset($params['loginToken']) && in_array($route['_route'], ['my_course_show', 'course_show', 'classroom_show', 'item_bank_exercise_show'])) {
            $pathInfo .= '/loginToken/' . $params['loginToken'];
        }

        if (in_array($route['_route'], ['classroom_reviews', 'classroom_introductions'])) {
            $pathInfo = $this->container->get('router')->generate('classroom_show', ['id' => $route['id']], UrlGeneratorInterface::ABSOLUTE_PATH);
        }

        if ('course_set_explore' === $route['_route']) {
            $query = [];
            $pathInfo = $this->container->get('router')->generate('course_set_explore', [], UrlGeneratorInterface::ABSOLUTE_PATH);
        }
        if ('task_live_entry' === $route['_route']) {
            $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($route['courseId'], $route['activityId']);
            $params = [
                'courseId' => $route['courseId'],
                'taskId' => $task['id'],
                'type' => $task['type'],
                'title' => $task['title'],
            ];
            $query = http_build_query($params);
            $pathInfo = '/live';
        }

        if ('goods_show' === $route['_route']) {
            $query = http_build_query($params);
            $pathInfo = "/goods/{$route['id']}/show";
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

    protected function trans($id, array $parameters = [])
    {
        return $this->container->get('translator')->trans($id, $parameters);
    }

    protected function getBiz()
    {
        return $this->container->get('biz');
    }
}
