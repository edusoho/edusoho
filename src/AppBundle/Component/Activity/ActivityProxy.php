<?php

namespace AppBundle\Component\Activity;

use Biz\Activity\BaseActivityExt;
use Codeages\Biz\Framework\Context\Biz;

class ActivityProxy
{
    public $activityConfig;

    public $activityContext;

    private $activityDir;

    /**
     * @var \AppBundle\Component\Activity\ActivityRuntimeContainerV1
     */
    private $container;

    private $allowedExt = array(
        'php', 'html', 'twig'
    );

    public function __construct(ActivityRuntimeContainerV1 $container, $activity, $activityDir)
    {
        $this->activityDir = $activityDir;
        $this->activityConfig = new ActivityConfig($activityDir.DIRECTORY_SEPARATOR.'activity.json');
        $this->activityContext = new ActivityContext($container->getBiz(), $activity);
        $this->container = $container;
    }

    public function renderRoute($routeName, ActivityProxy $activityProxy, $parameters = array())
    {
        $routeInfo = $activityProxy->getRouteInfo($routeName);
        switch ($routeInfo['extension']) {
            case 'php':
                $resp = $this->renderPhp($routeInfo['absolutePath']);
                break;
            case 'html':
            case 'twig':
                $resp = $this->render($routeInfo['relativePath'], $parameters);
                break;
            default:
                throw new \RuntimeException('Bad route info in activity');
        }

        return $resp;
    }

    public function render($relativePathView, array $parameters = array())
    {
        $parameters = array_merge(array('activityContext' => $this->activityContext), $parameters);
        return $this->container->render($this->getViewPath($relativePathView), $parameters);
    }

    public function renderPhp($absolutePath)
    {
        if (!file_exists($absolutePath)) {
            throw new \RuntimeException('The php file not found.');
        }

        return require $absolutePath;
    }

    public function getRouteInfo($routeName)
    {
        if (!empty($this->activityConfig['routes']) && !empty($this->activityConfig['routes'][$routeName])) {

            $relativePath = $this->activityConfig['routes'][$routeName];
            $pathInfo = pathinfo($relativePath);

            if (!in_array($pathInfo['extension'], $this->allowedExt)) {
                throw new \RuntimeException('Bad file extension in routes, please check.');
            }

            return array(
                'extension' => $pathInfo['extension'],
                'absolutePath' => $this->getAbsolutePath($pathInfo['extension'], $relativePath),
                'relativePath' => $relativePath,
            );
        } else {
            $defaultRelativePath = $routeName.'.html';
            return array(
                'extension' => 'html',
                'absolutePath' => $this->getAbsolutePath('html', $defaultRelativePath),
                'relativePath' => $defaultRelativePath,
            );
        }
    }

    private function getAbsolutePath($extension, $relativePath)
    {
        if ($extension === 'php') {
            return implode(DIRECTORY_SEPARATOR, array($this->activityDir, $relativePath));
        } else {
            return $this->getViewPath($relativePath);
        }
    }

    private function getViewPath($relativePath)
    {
        return implode(DIRECTORY_SEPARATOR, array('@activity', $this->activityConfig['type'], 'resources', 'views', $relativePath));
    }

}