<?php
namespace Topxia\WebBundle\Twig\Extension;

use Topxia\Service\Common\ServiceKernel;
use AppBundle\Controller\Course\CourseShowMetas;

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
        switch ($mode) {
            case 'guest':
                return CourseShowMetas::getGuestCourseShowMetas();
            case 'member':
                return CourseShowMetas::getMemberCourseShowMetas();
        }
    }

    public function getName()
    {
        return 'topxia_course_twig';
    }
}