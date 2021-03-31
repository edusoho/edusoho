<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Classroom\Service\ClassroomService;
use ApiBundle\Api\Annotation\ResponseFilter;
use Biz\Course\MemberException;

class MeClassroomMember extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\Classroom\ClassroomMemberFilter", mode="simple")
     */
    public function get(ApiRequest $request, $classroomId)
    {
        $member = $this->getClassroomService()->getClassroomMember($classroomId, $this->getCurrentUser()->getId());

        if ($member) {
            $member['access'] = $this->getClassroomService()->canLearnClassroom($classroomId);
            $member['expire'] = $this->getClassroomMemberExpire($member);
        }

        return $member;
    }

    public function remove(ApiRequest $request, $classroomId)
    {
        $reason = $request->request->get('reason', '从移动端退出班级');

        $user = $this->getCurrentUser();

        $this->getClassroomService()->tryTakeClassroom($classroomId);

        $member = $this->getClassroomService()->getClassroomMember($classroomId, $user->getId());

        if (empty($member)) {
            throw MemberException::NOTFOUND_MEMBER();
        }

        $this->getClassroomService()->removeStudent($classroomId, $user->getId(), array(
           'reason' => $reason,
        ));

        return array('success' => true);
    }

    private function getClassroomMemberExpire($member)
    {
        $classroom = $this->getClassroomService()->getClassroom($member['classroomId']);
        if (empty($classroom) || empty($member) || $classroom['status'] != 'published') {
            return [
                'status' => false,
                'deadline' => 0
            ];
        }

        if ($classroom['expiryMode'] == 'forever' && empty($member['levelId'])) {
            return [
                'status' => true,
                'deadline' => $member['deadline']
            ];
        }

        $deadline = $member['deadline'];

        // 比较:学员有效期和班级有效期
        $classroomDeadline = $this->getClassroomDeadline($classroom);
        if ($classroomDeadline) {
            $deadline = $deadline < $classroomDeadline ? $deadline : $classroomDeadline;
        }

        // 会员加入情况下的有效期
        if (!empty($member['levelId'])) {
            $deadline = $this->getVipDeadline($classroom, $member, $deadline);
        }

        if (empty($deadline)) {
            return [
                'status' => $deadline < time() ? false : true
            ];
        }

        return [
            'status' => $deadline < time() ? false : true,
            'deadline' => $deadline
        ];
    }

    private function getClassroomDeadline($classroom)
    {
        $deadline = 0;
        if ('date' == $classroom['expiryMode']) {
            $deadline = $classroom['expiryEndDate'];
        }

        return $deadline;
    }

    private function getVipDeadline($classroom, $member, $deadline)
    {
        $vipApp = $this->getAppService()->getAppByCode('vip');
        if (empty($vipApp)) {
            return 0;
        }

        $status = $this->getVipService()->checkUserInMemberLevel($member['userId'], $classroom['vipLevelId']);
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

    private function getAppService()
    {
        return $this->service('CloudPlatform:AppService');
    }

    protected function getVipService()
    {
        return $this->service('VipPlugin:Vip:VipService');
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }
}
