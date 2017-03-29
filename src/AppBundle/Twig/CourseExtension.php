<?php

namespace AppBundle\Twig;

use AppBundle\Util\AvatarAlert;
use Biz\Course\Service\MemberService;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CourseExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Biz
     */
    protected $biz;

    public function __construct(ContainerInterface $container, Biz $biz)
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
            new \Twig_SimpleFunction('course_show_metas', array($this, 'getCourseShowMetas')),
            new \Twig_SimpleFunction('is_buy_course_from_modal', array($this, 'isBuyCourseFromModal')),
            new \Twig_SimpleFunction('buy_course_need_approve', array($this, 'isUserApproval')),
            new \Twig_SimpleFunction('is_member_expired', array($this, 'isMemberExpired')),
        );
    }

    public function isMemberExpired($course, $member)
    {
        if (empty($course) || empty($member)) {
            return false;
        }

        return !$this->getMemberService()->isMemberNonExpired($course, $member);
    }

    public function getCourseShowMetas($mode = 'guest')
    {
        $metas = $this->container->get('course.extension')->getCourseShowMetas();

        return $metas["for_{$mode}"];
    }

    public function isBuyCourseFromModal($courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $user = $this->biz['user'];

        return !$user->isLogin()
            || $this->shouldUserinfoFill()
            || $this->isUserApproval($courseId)
            || $this->isUserAvatarEmpty();
    }

    public function isUserApproval($courseId)
    {
        $user = $this->biz['user'];
        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            return false;
        }

        return $course['approval'] && $user['approvalStatus'] !== 'approved';
    }

    protected function isUserAvatarEmpty()
    {
        $user = $this->biz['user'];

        return AvatarAlert::alertJoinCourse($user);
    }

    public function shouldUserinfoFill()
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

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }

    public function getName()
    {
        return 'topxia_course_twig';
    }
}
