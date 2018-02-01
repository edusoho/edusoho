<?php

namespace AppBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class TestpaperExtension extends \Twig_Extension
{
    protected $biz;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct($container, $biz)
    {
        $this->container = $container;
        $this->biz = $biz;
    }

    public function getFilters()
    {
        return array();
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('find_course_set_testpapers', array($this, 'findTestpapersByCourseSetId')),
            new \Twig_SimpleFunction('get_features', array($this, 'getFeatures')),
        );
    }

    public function findTestpapersByCourseSetId($id)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($id);
        $conditions = array(
            'courseSetId' => $id,
            'status' => 'open',
            'type' => 'testpaper',
        );

        if ($courseSet['parentId'] > 0 && $courseSet['locked']) {
            $conditions['copyIdGT'] = 0;
        }

        $testpapers = $this->getTestpaperService()->searchTestpapers(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        return $testpapers;
    }

    public function getFeatures()
    {
        return $this->container->hasParameter('enabled_features') ? $this->container->getParameter('enabled_features') : array();
    }

    protected function getTestpaperService()
    {
        return $this->biz->service('Testpaper:TestpaperService');
    }

    protected function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }

    public function getName()
    {
        return 'web_testpaper_twig';
    }
}
