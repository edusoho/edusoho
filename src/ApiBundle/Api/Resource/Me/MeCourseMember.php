<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\MemberException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;

class MeCourseMember extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\Course\CourseMemberFilter", mode="public"))
     */
    public function get(ApiRequest $request, $courseId)
    {
        $courseMember = $this->getCourseMemberService()->getCourseMember($courseId, $this->getCurrentUser()->getId());
        $this->getOCUtil()->single($courseMember, ['userId']);

        if ($courseMember) {
            $courseMember['access'] = $this->getCourseService()->canLearnCourse($courseId);
            $courseMember['expire'] = $this->getCourseMemberExpire($courseMember);
        }

        // 获取班级课程有效期
        $course = $this->getCourseService()->getCourse($courseId);
        if ($course['parentId']) {
            $classroom = $this->getClassroomService()->getClassroomByCourseId($course['id']);
            $classroomMember = $this->invokeResource(new ApiRequest("/api/me/classroom_members/{$classroom['id']}", 'GET'));

            if (!empty($classroomMember['expire'])) {
                $courseMember['expire']['status'] = $classroomMember['expire']['status'];
                $courseMember['expire']['deadline'] = empty($classroomMember['expire']['deadline']) ? 0 : strtotime($classroomMember['expire']['deadline']);
            }
        }

        return $courseMember;
    }

    public function remove(ApiRequest $request, $courseId)
    {
        $reason = $request->request->get('reason', '从App退出课程');

        $user = $this->getCurrentUser();

        $this->getCourseService()->tryTakeCourse($courseId);

        $member = $this->getCourseMemberService()->getCourseMember($courseId, $user->getId());

        if (empty($member)) {
            throw MemberException::NOTFOUND_MEMBER();
        }

        $this->getCourseMemberService()->removeStudent($courseId, $user->getId(), [
           'reason' => $reason,
        ]);

        return ['success' => true];
    }

    private function getCourseMemberExpire($member)
    {
        $course = $this->getCourseService()->getCourse($member['courseId']);
        if (empty($course) || empty($member)) {
            return [
                'status' => 0,
                'deadline' => 0,
            ];
        }

        if ('forever' == $course['expiryMode'] && 'vip_join' != $member['joinedChannel']) {
            return [
                'status' => 1,
                'deadline' => $member['deadline'],
            ];
        }

        $deadline = $member['deadline'];

        // 比较:学员有效期和课程有效期
        $courseDeadline = $this->getCourseDeadline($course);
        if ($courseDeadline) {
            $deadline = $deadline < $courseDeadline ? $deadline : $courseDeadline;
        }

        // 会员加入情况下的有效期
        if ('vip_join' == $member['joinedChannel']) {
            $deadline = $this->getVipDeadline($course, $member, $deadline);
        }

        return [
            'status' => $deadline < time() ? 0 : 1,
            'deadline' => $deadline,
        ];
    }

    private function getCourseDeadline($course)
    {
        $deadline = 0;
        if ('date' == $course['expiryMode'] || 'end_date' == $course['expiryMode']) {
            $deadline = $course['expiryEndDate'];
        }

        return $deadline;
    }

    private function getVipDeadline($course, $member, $deadline)
    {
        $vipApp = $this->getAppService()->getAppByCode('vip');
        if (empty($vipApp)) {
            return 0;
        }

        $status = $this->getVipService()->checkUserVipRight($member['user']['id'], 'course', $course['id']);
        if ('ok' !== $status) {
            return 0;
        }

        $vip = $this->getVipService()->getMemberByUserId($member['user']['id']);
        if (!$deadline) {
            return $vip['deadline'];
        } else {
            return $deadline < $vip['deadline'] ? $deadline : $vip['deadline'];
        }
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    private function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }

    private function getAppService()
    {
        return $this->service('CloudPlatform:AppService');
    }

    protected function getVipService()
    {
        return $this->service('VipPlugin:Vip:VipService');
    }

    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }
}
