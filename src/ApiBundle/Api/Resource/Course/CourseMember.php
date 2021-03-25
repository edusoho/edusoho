<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Exception\UnableJoinException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CourseMember extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $courseId)
    {
        $conditions = $request->query->all();
        $conditions['courseId'] = $courseId;
        $conditions['locked'] = 0;

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $members = $this->service('Course:MemberService')->searchMembers(
            $conditions,
            ['createdTime' => 'DESC'],
            $offset,
            $limit
        );

        $total = $this->service('Course:MemberService')->countMembers($conditions);

        $this->getOCUtil()->multiple($members, ['userId']);

        return $this->makePagingObject($members, $total, $offset, $limit);
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $courseId, $userId)
    {
        $member = $this->getMemberService()->getCourseMember($courseId, $userId);
        $member['expire'] = $this->getCourseMemberExpire($member);
        $this->getOCUtil()->single($member, ['userId']);

        return $member;
    }

    private function getCourseMemberExpire($member)
    {
        $course = $this->getCourseService()->getCourse($member['courseId']);
        if (empty($course) || empty($member) || $course['status'] != 'published') {
            return [
                'status' => false,
                'deadline' => 0
            ];
        }

        if ($course['expiryMode'] == 'forever' && empty($member['levelId'])) {
            return [
                'status' => true,
                'deadline' => $member['deadline']
            ];
        }

        $deadline = $member['deadline'];

        // 比较:学员有效期和课程有效期
        $courseDeadline = $this->getCourseDeadline($course);
        if ($courseDeadline) {
            $deadline = $deadline < $courseDeadline ? $deadline : $courseDeadline;
        }

        // 会员加入情况下的有效期
        if (!empty($member['levelId'])) {
            $deadline = $this->getVipDeadline($course, $member, $deadline);
        }

        return [
            'status' => $deadline < time() ? false : true,
            'deadline' => $deadline
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

        $status = $this->getVipService()->checkUserInMemberLevel($member['userId'], $course['vipLevelId']);
        if ('ok' !== $status) {
            return 0;
        }

        $vip = $this->getVipService()->getMemberByUserId($member['userId']);
        if (!$deadline) {
            return $vip['deadline'];
        } else {
            return $deadline < $vip['deadline'] ? $deadline : $vip['deadline'];
        }
    }

    public function add(ApiRequest $request, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (!$course) {
            throw CourseException::NOTFOUND_COURSE();
        }

        $member = $this->getMemberService()->getCourseMember($courseId, $this->getCurrentUser()->getId());

        if (!$member) {
            $member = $this->tryJoin($course);
        }

        if ($member) {
            $this->getOCUtil()->single($member, ['userId']);

            return $member;
        }

        return null;
    }

    private function tryJoin($course)
    {
        try {
            $this->getCourseService()->tryFreeJoin($course['id']);
        } catch (UnableJoinException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e, $e->getCode());
        }

        $member = $this->getMemberService()->getCourseMember($course['id'], $this->getCurrentUser()->getId());
        if (!empty($member)) {
            $this->getLogService()->info('course', 'join_course', "加入 教学计划《{$course['title']}》", ['courseId' => $course['id'], 'title' => $course['title'] ? $course['title'] : $course['courseSetTitle']]);
        }

        return $member;
    }

    /**
     * @return \Biz\System\Service\Impl\LogServiceImpl
     */
    private function getLogService()
    {
        return $this->service('System:LogService');
    }

    /**
     * @return MemberService
     */
    private function getMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    private function getAppService()
    {
        return $this->service('CloudPlatform:AppService');
    }

    protected function getVipService()
    {
        return $this->service('VipPlugin:Vip:VipService');
    }
}
