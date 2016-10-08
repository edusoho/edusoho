<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class MeChatroomes extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 10);

        $user               = $this->getCurrentUser();
        $classRoomChatrooms = $this->getClassRoomChatrooms($user['id'], $start, $limit);
        $courseChatrooms    = $this->getCourseChatrooms($user['id'], $start, $limit);

        $chatrooms = array_merge($classRoomChatrooms, $courseChatrooms);
        return $this->wrap($this->filter($chatrooms), count($chatrooms));
    }

    private function getClassRoomChatrooms($userId, $start, $limit)
    {
        $conditions = array('userId' => $userId);
        $total      = $this->getClassroomService()->searchMemberCount($conditions);
        $members    = $this->getClassroomService()->searchMembers($conditions, array('createdTime', 'DESC'), 0, $total);

        $classroomIds = ArrayToolkit::column($members, 'classroomId');

        $classrooms = $this->getClassroomService()->findClassroomsByIds($classroomIds);

        $conditions = array(
            'targetIds'  => $classroomIds,
            'targetType' => 'classroom',
            'userId'     => $userId
        );
        $conversations = $this->getConversationService()->searchConversations($conditions, array('createdTime', 'DESC'), $start, $limit);

        $chatrooms = array();
        foreach ($conversations as $conversation) {
            if (!isset($classrooms[$conversation['targetId']])) {
                continue;
            }

            $classroom = $classrooms[$conversation['targetId']];

            $chatrooms[] = array(
                'type'    => 'classroom',
                'id'      => $classroom['id'],
                'title'   => $classroom['title'],
                'picture' => $this->getFileUrl($classroom['smallPicture'])
            );
        }

        return $chatrooms;
    }

    private function getCourseChatrooms($userId, $start, $limit)
    {
        $conditions = array('userId' => $userId);
        $total      = $this->getCourseService()->searchMemberCount($conditions);
        $members    = $this->getCourseService()->searchMembers($conditions, array('createdTime', 'DESC'), 0, $total);

        $courseIds = ArrayToolkit::column($members, 'courseId');
        $courses   = $this->getCourseService()->searchCourses(
            array('courseIds' => $courseIds, 'parentId' => 0),
            array('createdTime', 'DESC'),
            0, PHP_INT_MAX
        );
        $courses = ArrayToolkit::index($courses, 'id');

        $conditions = array(
            'targetIds'  => ArrayToolkit::column($courses, 'id'),
            'targetType' => 'course',
            'userId'     => $userId
        );
        $conversations = $this->getConversationService()->searchConversations($conditions, array('createdTime', 'DESC'), $start, $limit);

        $chatrooms = array();
        foreach ($conversations as $conversation) {
            if (!isset($courses[$conversation['targetId']])) {
                continue;
            }

            $course = $courses[$conversation['targetId']];

            $chatrooms[] = array(
                'type'    => 'course',
                'id'      => $course['id'],
                'title'   => $course['title'],
                'picture' => $this->getFileUrl($course['smallPicture'])
            );
        }

        return $chatrooms;
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
