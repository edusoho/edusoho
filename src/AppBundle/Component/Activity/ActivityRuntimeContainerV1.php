<?php

namespace AppBundle\Component\Activity;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ActivityRuntimeContainerV1 implements ActivityRuntimeContainerInterface
{
    const VERSION = '1.0.0';

    private $container;

    private $biz;

    private static $instance;

    public $activityDir;

    public $activityProxy;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    public $request;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->biz = $container->get('biz');
        $this->activityDir = implode(DIRECTORY_SEPARATOR, array($container->getParameter('kernel.root_dir'), '..', 'activities'));
        $this->request = $container->get('request');
    }

    public function show($activity)
    {

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

    public function invoke($activityType, $action)
    {
        $actionFile = implode(DIRECTORY_SEPARATOR, array(
            $this->activityDir, $activityType, 'src', $action
        ));

        if (!file_exists($actionFile)) {
            throw new BadRequestHttpException('The activity not found.');
        }

        return require $actionFile;
    }

    public function initActivityProxy($task)
    {
        $activityProxy = new ActivityProxy();
        $activityProxy->task = $task;
        $this->activityProxy = $activityProxy;
    }

    /**
     * @return \Codeages\Biz\Framework\Dao\Connection
     */
    public function db()
    {
        return $this->biz['db'];
    }

    public function render($template)
    {

    }
}