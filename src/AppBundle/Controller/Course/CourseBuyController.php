<?php

namespace AppBundle\Controller\Course;

use AppBundle\Controller\BuyFlowController;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use VipPlugin\Biz\Marketing\VipRightSupplier\CourseVipRightSupplier;

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
            $vipJoinEnabled = 'ok' === $this->getVipService()->checkUserVipRight($user['id'], CourseVipRightSupplier::CODE, $course['id']);
        }

        return !$course['isFree'] && !$payment['enabled'] && !$vipJoinEnabled;
    }

    protected function tryFreeJoin($id)
    {
        $this->getCourseService()->tryFreeJoin($id);
    }

    protected function getSuccessUrl($id)
    {
        return $this->generateUrl('my_course_show', ['id' => $id]);
    }

    protected function needNoStudentNumTip($id)
    {
        $course = $this->getCourseService()->getCourse($id);

        return (empty($course['maxStudentNum']) || $course['maxStudentNum'] - $course['studentNum'] <= 0) && 'live' == $course['type'];
    }

    protected function needApproval($id)
    {
        $course = $this->getCourseService()->getCourse($id);
        $user = $this->getCurrentUser();

        return $course['approval'] && 'approved' !== $user['approvalStatus'];
    }

    protected function isJoined($id)
    {
        $user = $this->getUser();
        $member = $this->getCourseMemberService()->getCourseMember($id, $user['id']);
        if (!empty($member)) {
            $course = $this->getCourseService()->getCourse($id);
            $this->getLogService()->info('course', 'join_course', "加入教学计划《{$course['title']}》", ['userId' => $user['id'], 'courseId' => $course['id'], 'title' => ($course['title']) ? $course['title'] : $course['courseSetTitle']]);
        }

        return $member;
    }

    protected function needInformationCollectionBeforeJoin($targetId)
    {
        $course = $this->getCourseService()->getCourse($targetId);
        if ($this->isPluginInstalled('Vip')) {
            $vipRight = $this->getVipRightService()->getVipRightBySupplierCodeAndUniqueCode(CourseVipRightSupplier::CODE, $course['id']);
            if ((1 != $course['isFree'] || 0 != $course['originPrice']) && empty($vipRight)) {
                return [];
            }
        } else {
            if ((1 != $course['isFree'] || 0 != $course['originPrice'])) {
                return [];
            }
        }

        $location = ['targetType' => 'course', 'targetId' => $targetId];
        if ('0' != $targetId) {
            $course = $this->getCourseService()->getCourse($targetId);
            $location['targetId'] = $course['courseSetId'];
        }

        $event = $this->getInformationCollectEventService()->getEventByActionAndLocation('buy_before', $location);

        if (empty($event)) {
            return [];
        }

        $url = $this->generateUrl('information_collect_event', [
            'eventId' => $event['id'],
            'goto' => $this->generateUrl('course_buy', ['id' => $targetId]),
        ]);

        return [$event['id'], 'url' => $url];
    }

    protected function needInformationCollectionAfterJoin($targetId)
    {
        $location = ['targetType' => 'course', 'targetId' => $targetId];
        if ('0' != $targetId) {
            $course = $this->getCourseService()->getCourse($targetId);
            $location['targetId'] = $course['courseSetId'];
        }

        $event = $this->getInformationCollectEventService()->getEventByActionAndLocation('buy_after', $location);

        if (empty($event)) {
            return [];
        }

        $url = $this->generateUrl('information_collect_event', [
            'eventId' => $event['id'],
            'goto' => $this->getSuccessUrl($targetId),
        ]);

        return [$event['id'], 'url' => $url];
    }

    protected function getInformationCollectResultService()
    {
        return $this->createService('InformationCollect:ResultService');
    }

    protected function getInformationCollectEventService()
    {
        return $this->createService('InformationCollect:EventService');
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
