<?php
namespace Topxia\WebBundle\Twig\Extension;

use Topxia\Service\Common\ServiceKernel;

class CourseExtension extends \Twig_Extension
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return array(
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('course_show_metas', array($this, 'getCourseShowMetas'), array('is_safe' => array('html')))
        );
    }

    public function getCourseShowMetas($mode = 'guest')
    {   
        $metas = $this->container->get('extension.default')->getCourseShowMetas();
        return $metas["for_{$mode}"];
    }



    public function getName()
    {
        return 'topxia_course_twig';
    }
}