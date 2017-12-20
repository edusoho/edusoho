<?php

namespace AppBundle\Component\Activity;

class ActivityProxy
{
    public $routeName;

    private $activityConfig;

    private $activityDir;

    private $allowedExt = array(
        'php', 'html', 'twig'
    );

    public function __construct($activity, $activityDir)
    {
        $this->activityDir = $activityDir;
        $this->activityConfig = new ActivityConfig($activityDir.DIRECTORY_SEPARATOR.'activity.json');
    }

    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;
    }

    public function getRouteInfo()
    {
        if (!empty($this->activityConfig['routes']) && !empty($this->activityConfig['routes'][$this->routeName])) {

            $relativePath = $this->activityConfig['routes'][$this->routeName];
            $pathInfo = pathinfo($relativePath);

            if (!in_array($pathInfo['extension'], $this->allowedExt)) {
                throw new \RuntimeException('Bad file extension in routes, please check.');
            }

            return array(
                'extension' => $pathInfo['extension'],
                'realPath' => $this->getRealFilePath($pathInfo['extension'], $relativePath),
                'relativePath' => $relativePath,
            );
        } else {
            $defaultRelativePath = 'resources'.DIRECTORY_SEPARATOR.$this->routeName.'.html';
            return array(
                'extension' => 'html',
                'realPath' => $this->getRealFilePath('html', $defaultRelativePath),
                'relativePath' => $defaultRelativePath,
            );
        }
    }

    private function getRealFilePath($extension, $relativePath)
    {
        if ($extension === 'php') {
            return $this->activityDir.DIRECTORY_SEPARATOR.$relativePath;
        } else {
            return implode(DIRECTORY_SEPARATOR, array('@activity', $this->activityConfig['type'], 'resources', 'views', $relativePath));
        }
    }

}