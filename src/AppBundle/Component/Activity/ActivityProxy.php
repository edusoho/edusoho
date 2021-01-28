<?php

namespace AppBundle\Component\Activity;

use AppBundle\Common\Exception\InvalidArgumentException;

class ActivityProxy
{
    private $activityConfig;

    private $activityContext;

    /**
     * @var \AppBundle\Component\Activity\ActivityRuntimeContainer
     */
    private $container;

    private $allowedExt = array(
        'php', 'html', 'twig',
    );

    public function __construct(ActivityRuntimeContainer $container, $activity, ActivityConfig $activityConfig)
    {
        $this->activityConfig = $activityConfig;
        $this->activityContext = new ActivityContext($container->getBiz(), $activity);
        $this->container = $container;
    }

    /**
     * @return \AppBundle\Component\Activity\ActivityConfig
     */
    public function getActivityConfig()
    {
        return $this->activityConfig;
    }

    /**
     * @return \AppBundle\Component\Activity\ActivityContext
     */
    public function getActivityContext()
    {
        return $this->activityContext;
    }

    public function renderRoute($routeName, $parameters = array())
    {
        $routeInfo = $this->getRouteInfo($routeName);
        switch ($routeInfo['extension']) {
            case 'php':
                $resp = $this->renderPhp($routeInfo['absolutePath']);
                break;
            case 'html':
            case 'twig':
                $resp = $this->render($routeInfo['relativePath'], $parameters);
                break;
            default:
                throw new InvalidArgumentException('Bad route info in activity');
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
            throw new InvalidArgumentException('The php file not found.');
        }

        return require $absolutePath;
    }

    public function getRouteInfo($routeName)
    {
        if (!empty($this->activityConfig['routes']) && !empty($this->activityConfig['routes'][$routeName])) {
            $relativePath = $this->activityConfig['routes'][$routeName];
            $pathInfo = pathinfo($relativePath);

            if (!in_array($pathInfo['extension'], $this->allowedExt)) {
                throw new InvalidArgumentException('Bad file extension in routes, please check.');
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
        if ('php' === $extension) {
            return implode(DIRECTORY_SEPARATOR, array($this->activityConfig['dir'], $relativePath));
        } else {
            return $this->getViewPath($relativePath);
        }
    }

    private function getViewPath($relativePath)
    {
        return implode(DIRECTORY_SEPARATOR, array('@activity', $this->activityConfig['type'], 'resources', 'views', $relativePath));
    }
}
