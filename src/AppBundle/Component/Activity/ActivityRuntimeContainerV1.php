<?php

namespace AppBundle\Component\Activity;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ActivityRuntimeContainerV1 implements ActivityRuntimeContainerInterface
{
    const VERSION = '1.0.0';

    private $container;

    private $biz;

    private static $instance;

    public $activitiesDir;

    /**
     * @var \AppBundle\Component\Activity\ActivityProxy
     */
    public $activityProxy;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    public $request;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->biz = $container->get('biz');
        $this->activitiesDir = $container->getParameter('edusoho.activities_dir');
        $this->request = $container->get('request');
        self::$instance = $this;
    }

    public function show($activity)
    {
        $activityProxy = $this->createActivityProxy($activity);
        return $activityProxy->renderRoute(ActivityRuntimeContainerInterface::ROUTE_SHOW, $activityProxy, array(
            'activity' => $activity,
        ));
    }

    public function create($activity)
    {
        $activityProxy = $this->createActivityProxy($activity);
        return $activityProxy->renderRoute(ActivityRuntimeContainerInterface::ROUTE_CREATE, $activityProxy, array(
            'activity' => $activity,
        ));
    }

    public function update($activity)
    {
        // TODO: Implement update() method.
    }

    public function customRoute($activity, $routeName)
    {
        $activityProxy = $this->createActivityProxy($activity);
        return $activityProxy->renderRoute($routeName, $activityProxy, array(
            'activity' => $activity,
        ));
    }

    /**
     * @return \AppBundle\Component\Activity\ActivityRuntimeContainerV1
     */
    public static function instance()
    {
        return self::$instance;
    }

    public function getActivityProxy()
    {
        return $this->activityProxy;
    }

    private function createActivityProxy($activity)
    {
        $activityProxy = new ActivityProxy($this, $activity, $this->activitiesDir.DIRECTORY_SEPARATOR.$activity['mediaType']);
        $this->activityProxy = $activityProxy;
        return $activityProxy;
    }

    /**
     * @return \Codeages\Biz\Framework\Dao\Connection
     */
    public function db()
    {
        return $this->biz['db'];
    }

    public function getBiz()
    {
        return $this->biz;
    }

    public function createService($service)
    {
        return $this->biz->service($service);
    }

    public function createJsonResponse($data = null, $status = 200, $headers = array())
    {
        return new JsonResponse($data, $status, $headers);
    }

    public function render($view, array $parameters = array(), Response $response = null)
    {
        if ($this->container->has('templating')) {
            return $this->container->get('templating')->renderResponse($view, $parameters, $response);
        }

        if (!$this->container->has('twig')) {
            throw new \LogicException('You can not use the "render" method if the Templating Component or the Twig Bundle are not available.');
        }

        if (null === $response) {
            $response = new Response();
        }

        $response->setContent($this->container->get('twig')->render($view, $parameters));

        return $response;
    }
}