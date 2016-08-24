<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class MeChatroomes extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 10);

        $user = $this->getCurrentUser();

        $conditions = array('userId' => $user['id']);
        $total = $this->getClassroomService()->searchMemberCount($conditions);
        $members = $this->getClassroomService()->searchMembers($conditions, array('createdTime', 'DESC'), $start, $limit);

        $classroomIds = ArrayToolkit::column($members, 'classroomId');

        $classRoomChatrooms = $this->getClassRoomChatrooms($userId);
        $courseChatrooms = $this->getCourseChatrooms($userId);

        $chatrooms = array_merge($classRoomChatrooms, $courseChatrooms);
        return $this->wrap($this->filter($chatrooms), $total);
    }

    private function getClassRoomChatrooms($userId) {
        $conditions = array('userId' => $userId);
        $total = $this->getClassroomService()->searchMemberCount($conditions);
        $members = $this->getClassroomService()->searchMembers($conditions, array('createdTime', 'DESC'), $start, $total);

        $classroomIds = ArrayToolkit::column($members, 'classroomId');

        $classrooms = $this->getClassroomService()->findClassroomsByIds($classroomIds);

        $chatrooms = array();
        foreach ($classrooms as $classroom) {
            if (!isset($classrooms['conversationId']) || empty($classrooms['conversationId'])) {
                continue;
            }
            $chatrooms[] = array(
                'type' => 'classroom',
                'id' => $classroom['id'],
                'title' => $classroom['title'],
                'conversationId' => $classroom['conversationId'],
                'picture' => $this->getFileUrl($classroom['smallPicture']),
            );
        }

        return $chatrooms;
    }

    private function getCourseChatrooms($userId) {
        $conditions = array('userId' => $userId);
        $total = $this->getCourseService()->searchMemberCount($conditions);
        $members = $this->getCourseService()->searchMembers($conditions, array('createdTime', 'DESC'), $start, $total);

        $courseIds = ArrayToolkit::column($members, 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $chatrooms = array();
        foreach ($courses as $course) {
            if (!isset($course['conversationId']) || empty($course['conversationId'])) {
                continue;
            }
            if ($course['parentId'] != 0) {
                continue;
            }
            $chatrooms[] = array(
                'type' => 'course',
                'id' => $course['id'],
                'title' => $course['title'],
                'conversationId' => $course['conversationId'],
                'picture' => $this->getFileUrl($course['smallPicture']),
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
}
