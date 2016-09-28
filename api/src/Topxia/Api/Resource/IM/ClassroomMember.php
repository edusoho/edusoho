<?php

namespace Topxia\Api\Resource\IM;

use Silex\Application;
use Topxia\Api\Resource\BaseResource;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class ClassroomMember extends BaseResource
{
    public function post(Application $app, Request $request, $classroomId)
    {
        $user      = $this->getCurrentUser();
        $classroom = $this->getClassroomService()->getClassroom($classroomId);

        $classroomMember = $this->getClassroomService()->getClassroomMember($classroomId, $user['id']);
        if (!$classroomMember || in_array('auditor', $classroomMember['role'])) {
            return array();
        }

        $convNo = $this->createConversation($classroom, $user['id'], $user['nickname']);
        if (empty($convNo)) {
            return array();
        }

        $conversationMember = $this->getConversationService()->getMemberByConvNoAndUserId($convNo, $user['id']);

        if (!$conversationMember) {
            $res = $this->getConversationService()->addConversationMember($convNo, $user['id'], $user['nickname']);
            if ($res) {
                $member = array(
                    'convNo'     => $convNo,
                    'targetId'   => $classroom['id'],
                    'targetType' => 'classroom',
                    'userId'     => $user['id']
                );
                $conversationMember = $this->getConversationService()->addMember($member);
            }
        }

        return $this->filter($conversationMember);
    }

    public function filter($res)
    {
        return $res;
    }

    protected function createConversation($classroom, $userId, $nickname)
    {
        if (empty($classroom)) {
            return '';
        }

        if (empty($classroom['convNo'])) {
            $message = array(
                'name'    => $classroom['title'],
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

            $this->getClassroomService()->updateClassroom($classroom['id'], array('convNo' => $result['no']));
            return $result['no'];
        }

        return $classroom['convNo'];
    }

    protected function getConversationService()
    {
        return $this->getServiceKernel()->createService('IM.ConversationService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }
}
