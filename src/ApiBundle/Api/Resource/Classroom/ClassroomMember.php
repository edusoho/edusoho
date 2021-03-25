<?php

namespace ApiBundle\Api\Resource\Classroom;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Classroom\ClassroomException;
use Biz\Classroom\Service\ClassroomService;
use Biz\Exception\UnableJoinException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use ApiBundle\Api\Annotation\ApiConf;
use AppBundle\Common\ArrayToolkit;

class ClassroomMember extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $classroomId, $userId)
    {
        $member = $this->getClassroomService()->getClassroomMember($classroomId, $userId);
        $member['expire'] = $this->getClassroomMemberExpire($member);
        $this->getOCUtil()->single($member, ['userId']);

        return $member;
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

    public function add(ApiRequest $request, $classroomId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);

        if (!$classroom) {
            throw ClassroomException::NOTFOUND_CLASSROOM();
        }

        $member = $this->getClassroomService()->getClassroomMember($classroomId, $this->getCurrentUser()->getId());
        if (!$member || $member['role'] == array('auditor')) {
            $member = $this->tryJoin($classroom);
        }

        if ($member) {
            $this->getOCUtil()->single($member, array('userId'));
            $member['isOldUser'] = true;

            return $member;
        }

        return null;
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $classroomId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);

        if (!$classroom) {
            throw ClassroomException::NOTFOUND_CLASSROOM();
        }

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $conditions = array('classroomId' => $classroomId);

        if ($request->query->get('role', '')) {
            $conditions['role'] = $request->query->get('role');
        }

        $total = $this->getClassroomService()->searchMemberCount($conditions);
        $members = $this->getClassroomService()->searchMembers($conditions, array('createdTime' => 'DESC'), $offset, $limit);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($members, 'userId'));
        foreach ($members as &$member) {
            $member['user'] = empty($users[$member['userId']]) ? null : $users[$member['userId']];
        }

        return $this->makePagingObject($members, $total, $offset, $limit);
    }

    private function tryJoin($classroom)
    {
        try {
            $this->getClassroomService()->tryFreeJoin($classroom['id']);
        } catch (UnableJoinException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        $member = $this->getClassroomService()->getClassroomMember($classroom['id'], $this->getCurrentUser()->getId());
        if (!empty($member)) {
            $this->getLogService()->info('classroom', 'join_classroom', "加入班级《{$classroom['title']}》", array('classroomId' => $classroom['id'], 'title' => $classroom['title']));
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
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }

    protected function getUserService()
    {
        return $this->service('User:UserService');
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
