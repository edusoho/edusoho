<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class MeChatroomes extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 10);

        $user      = $this->getCurrentUser();
        $chatrooms = array();

        $conditions = array(
            'userId'      => $user['id'],
            'targetTypes' => array('course', 'classroom')
        );
        $conversations = $this->getConversationService()->searchImMembers($conditions, array('createdTime', 'DESC'), $start, $limit);

        if (!$conversations) {
            return $this->wrap($chatrooms, 0);
        }

        foreach ($conversations as $conversation) {
            if ($conversation['targetType'] == 'course') {
                $course = $this->getCourseService()->getCourse($conversation['targetId']);
                if (!$course || $course['parentId'] > 0) {
                    continue;
                }
                $chatrooms[] = array(
                    'type'    => 'course',
                    'id'      => $course['id'],
                    'title'   => $course['title'],
                    'convNo'  => $conversation['convNo'],
                    'picture' => $this->getFileUrl($course['smallPicture'])
                );
            } elseif ($conversation['targetType'] == 'classroom') {
                $classroom = $this->getClassroomService()->getClassroom($conversation['targetId']);
                if (!$classroom) {
                    continue;
                }

                $chatrooms[] = array(
                    'type'    => 'classroom',
                    'id'      => $classroom['id'],
                    'title'   => $classroom['title'],
                    'convNo'  => $conversation['convNo'],
                    'picture' => $this->getFileUrl($classroom['smallPicture'])
                );
            }
        }

        return $this->wrap($this->filter($chatrooms), count($chatrooms));
    }

    public function filter($res)
    {
        return $res;
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
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
