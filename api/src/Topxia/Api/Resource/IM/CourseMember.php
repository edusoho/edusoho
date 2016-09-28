<?php

namespace Topxia\Api\Resource\IM;

use Silex\Application;
use Topxia\Api\Resource\BaseResource;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class CourseMember extends BaseResource
{
    public function post(Application $app, Request $request, $courseId)
    {
        $user   = $this->getCurrentUser();
        $course = $this->getCourseService()->getCourse($courseId);

        $convNo = $this->createConversation($course);
        if (empty($convNo)) {
            return array();
        }

        $courseMember = $this->getCourseService()->getCourseMember($courseId, $user['id']);

        $conversationMember = $this->getConversationService()->getMemberByConvNoAndUserId($convNo, $user['id']);

        if ($courseMember && !$conversationMember) {
            $res = $this->getConversationService()->addConversationMember($convNo, $user['id'], $user['nickname']);

            if ($res) {
                $member = array(
                    'convNo'     => $convNo,
                    'targetId'   => $course['id'],
                    'targetType' => 'course',
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

    protected function createConversation($course)
    {
        if (empty($course)) {
            return '';
        }

        if (empty($course['convNo'])) {
            $user    = $this->getUserService()->getUser($course['userId']);
            $message = array(
                'name'    => $course['title'],
                'clients' => array(
                    array(
                        'clientId'   => $course['userId'],
                        'clientName' => $user['nickname']
                    )
                )
            );

            $result = CloudAPIFactory::create('root')->post('/im/me/conversation', $message);
            if (isset($result['error'])) {
                return '';
            }

            $this->getCourseService()->updateCourse($course['id'], array('convNo' => $result['no']));
            return $result['no'];
        }

        return $course['convNo'];
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getConversationService()
    {
        return $this->getServiceKernel()->createService('IM.ConversationService');
    }
}
