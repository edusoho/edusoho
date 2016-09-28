<?php

namespace Topxia\Service\IM\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\IM\ConversationService;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class ConversationServiceImpl extends BaseService implements ConversationService
{
    public function getConversationByMemberIds(array $memberIds)
    {
        sort($memberIds);
        $memberHash = $this->buildMemberHash($memberIds);
        return $this->getConversationDao()->getConversationByMemberHash($memberHash);
    }

    public function addConversation($conversation)
    {
        $conversation = $this->filterConversationFields($conversation);

        if (count($conversation['memberIds']) < 2) {
            throw $this->createServiceException("Only support memberIds's count >= 2");
        }

        $conversation['memberHash']  = $this->buildMemberHash($conversation['memberIds']);
        $conversation['createdTime'] = time();

        return $this->getConversationDao()->addConversation($conversation);
    }

    public function conversationSync()
    {
        $courseSyncCount    = $this->courseSync();
        $classroomSyncCount = $this->classroomSync();

        return ($courseSyncCount + $classroomSyncCount);
    }

    public function getMember($id)
    {
        return $this->getConversationMemberDao()->getMember($id);
    }

    public function getMemberByConvNoAndUserId($convNo, $userId)
    {
        return $this->getConversationMemberDao()->getMemberByConvNoAndUserId($convNo, $userId);
    }

    public function findMembersByConvNo($convNo)
    {
        return $this->getConversationMemberDao()->findMembersByConvNo($convNo);
    }

    public function addMember($member)
    {
        $member['createdTime'] = time();
        return $this->getConversationMemberDao()->addMember($member);
    }

    public function deleteMember($id)
    {
        return $this->getConversationMemberDao()->deleteMember($id);
    }

    public function deleteMemberByConvNoAndUserId($convNo, $userId)
    {
        return $this->getConversationMemberDao()->deleteMemberByConvNoAndUserId($convNo, $userId);
    }

    public function addConversationMember($convNo, $userId, $nickname)
    {
        $clients = array(
            array('clientId' => $userId, 'clientName' => $nickname)
        );

        $res = CloudAPIFactory::create('root')->post("/im/conversations/{$convNo}/members", array('clients' => $clients));

        if (isset($res['success'])) {
            return true;
        }

        return false;
    }

    protected function createConversation($title, $userId, $nickname)
    {
        $message = array(
            'name'    => $title,
            'clients' => array(
                array(
                    'clientId'   => $userId,
                    'clientName' => $nickname
                )
            )
        );

        $result = CloudAPIFactory::create('root')->post('/im/me/conversation', $message);

        if (isset($result['error'])) {
            return '';
        }

        return $result['no'];
    }

    public function courseSync()
    {
        $unsyncCourses = $this->getCourseService()->findUnsyncConvParentIdCourses();

        $count = 0;
        if (!$unsyncCourses) {
            return $count;
        }

        $userIds = ArrayToolkit::column($unsyncCourses, 'userId');
        $users   = $this->getUserService()->findUsersByIds($userIds);

        foreach ($unsyncCourses as $course) {
            $convNo = $this->createConversation($course['title'], $course['userId'], $users[$course['userId']]['nickname']);
            if (!empty($convNo)) {
                $this->getCourseService()->updateCourse($course['id'], array('convNo' => $convNo));
                $count++;
            }
        }

        return $count;
    }

    protected function classroomSync()
    {
        $user  = $this->getCurrentUser();
        $count = 0;

        $unsyncClassrooms = $this->getClassroomService()->searchClassrooms(array('convNo' => ''), array('createdTime', 'DESC'), 0, PHP_INT_MAX);

        if (!$unsyncClassrooms) {
            return $count;
        }

        foreach ($unsyncClassrooms as $classroom) {
            $convNo = $this->createConversation($classroom['title'], $user['id'], $user['nickname']);
            if (!empty($convNo)) {
                $this->getClassroomService()->updateClassroom($classroom['id'], array('convNo' => $convNo));
                $count++;
            }
        }

        return $count;
    }

    protected function filterConversationFields(array $fields)
    {
        $fields = ArrayToolkit::parts($fields, array('no', 'memberIds'));

        if (empty($fields['no'])) {
            throw $this->createServiceException('field `no` can not be empty');
        }

        if (!is_array($fields['memberIds'])) {
            throw $this->createServiceException('field `memberIds` must be array');
        }
        if (empty($fields['memberIds'])) {
            throw $this->createServiceException('field `memberIds` can not be empty');
        }
        sort($fields['memberIds']);

        return $fields;
    }

    protected function buildMemberHash(array $memberIds)
    {
        return md5(join($memberIds, ','));
    }

    protected function filterMyConversationFields(array $fields)
    {
        $fields = ArrayToolkit::parts($fields, array(
            'no',
            'userId',
            'createdTime',
            'updatedTime'
        ));

        if (empty($fields['no'])) {
            throw $this->createServiceException('field `no` can not be empty');
        }

        if (empty($fields['userId'])) {
            throw $this->createServiceException('field `userId` can not be empty');
        }

        return $fields;
    }

    protected function getConversationDao()
    {
        return $this->createDao('IM.ConversationDao');
    }

    protected function getConversationMemberDao()
    {
        return $this->createDao('IM.ConversationMemberDao');
    }

    protected function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    protected function getClassroomService()
    {
        return $this->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }
}
