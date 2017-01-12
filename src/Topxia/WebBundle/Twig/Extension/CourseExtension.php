<?php
namespace Topxia\WebBundle\Twig\Extension;

use Topxia\Service\Common\ServiceKernel;

class CourseExtension extends \Twig_Extension
{
    protected $container;
    protected $biz;

    public function __construct($container, $biz)
    {
        $this->container = $container;
        $this->biz       = $biz;
    }

    public function getFilters()
    {
        return array(
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('course_show_metas', array($this, 'getCourseShowMetas')),
            new \Twig_SimpleFunction('is_buy_course_from_modal', array($this, 'isBuyCourseFromModal'))
        );
    }

    public function getCourseShowMetas($mode = 'guest')
    {   
        $metas = $this->container->get('extension.default')->getCourseShowMetas();
        return $metas["for_{$mode}"];
    }

    public function isBuyCourseFromModal($courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        return $this->shouldUserinfoFill() 
            || $this->isUserApproval($course) 
            || $this->isUserAvatarEmpty();
    }

    protected function isUserApproval($course)
    {
        $user = $this->biz['user'];
        return $course['approval'] && $user['approvalStatus'] != 'approved';
    }

    protected function isUserAvatarEmpty()
    {
        $user = $this->biz['user'];
        return $this->getSettingService()->get('user_partner.avatar_alert', 'close') == 'open' && empty($user['smallAvatar']);
    }

    protected function shouldUserinfoFill()
    {
        $setting = $this->getSettingService()->get('course');
        return !empty($setting['buy_fill_userinfo']);
    }

    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    public function getName()
    {
        return 'topxia_course_twig';
    }
}