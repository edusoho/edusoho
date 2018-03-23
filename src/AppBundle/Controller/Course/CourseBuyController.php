<?php

namespace AppBundle\Controller\Course;

use AppBundle\Controller\BuyFlowController;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;

class CourseBuyController extends BuyFlowController
{
    protected $targetType = 'course';

    protected function needOpenPayment($id)
    {
        $payment = $this->getSettingService()->get('payment');
        $course = $this->getCourseService()->getCourse($id);
        $vipJoinEnabled = false;
        if ($this->isPluginInstalled('Vip') && $this->setting('vip.enabled')) {
            $user = $this->getCurrentUser();
            $vipJoinEnabled = 'ok' === $this->getVipService()->checkUserInMemberLevel($user['id'], $course['vipLevelId']);
        }

        return !$course['isFree'] && !$payment['enabled'] && !$vipJoinEnabled;
    }

    protected function tryFreeJoin($id)
    {
        $this->getCourseService()->tryFreeJoin($id);
    }
    
    protected function getSuccessUrl($id)
    {
        return $this->generateUrl('my_course_show', array('id' => $id));
    }

    protected function needNoStudentNumTip($id)
    {
        $course = $this->getCourseService()->getCourse($id);

        return $course['maxStudentNum'] - $course['studentNum'] <= 0 && $course['type'] == 'live';
    }

    protected function needApproval($id)
    {
        $course = $this->getCourseService()->getCourse($id);
        $user = $this->getCurrentUser();

        return $course['approval'] && $user['approvalStatus'] !== 'approved';
    }

    protected function isJoined($id)
    {
        $user = $this->getUser();

        return $this->getCourseMemberService()->getCourseMember($id, $user['id']);
    }

    /**
     * @return MemberService
     */
    private function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return VipService
     */
    protected function getVipService()
    {
        return $this->createService('VipPlugin:Vip:VipService');
    }
}
