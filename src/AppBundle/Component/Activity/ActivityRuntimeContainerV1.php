<?php

namespace AppBundle\Component\Activity;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ActivityRuntimeContainerV1 implements ActivityRuntimeContainerInterface
{
    const VERSION = '1.0.0';

    private $container;

    private $biz;

    private static $instance;

    public $activitiesDir;

    public $activityProxy;

    public $activityConfig;

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
    }

    public function show($activity)
    {
        $activityProxy = $this->createActivityProxy($activity);
        $activityProxy->setRouteName(ActivityRuntimeContainerInterface::ROUTE_SHOW);
        return $this->renderRoute($activityProxy, array(
            'activity' => $activity,
        ));
    }

    private function renderRoute(ActivityProxy $activityProxy, $parameters = array())
    {
        $routeInfo = $activityProxy->getRouteInfo();
        switch ($routeInfo['extension']) {
            case 'php':
                $resp = $this->renderPhp($routeInfo['realPath']);
                break;
            case 'html':
            case 'twig':
                $resp = $this->render($routeInfo['realPath'], $parameters);
                break;
            default:
                throw new \RuntimeException('Bad route info in activity');
        }

        return $resp;
    }

    public function create()
    {
        // TODO: Implement create() method.
    }

    public function update($task)
    {
        // TODO: Implement update() method.
    }


    public static function instance()
    {
        return self::$instance;
    }

    public function getActivityProxy()
    {
        return $this->activityProxy;
    }

    public function renderPhp($realPath)
    {
        if (!file_exists($realPath)) {
            throw new \RuntimeException('The activity not found.');
        }

        return require $realPath;
    }

    private function createActivityProxy($activity)
    {
        return new ActivityProxy($activity, $this->activitiesDir.DIRECTORY_SEPARATOR.$activity['mediaType']);
    }

    /**
     * @return \Codeages\Biz\Framework\Dao\Connection
     */
    public function db()
    {
        return $this->biz['db'];
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