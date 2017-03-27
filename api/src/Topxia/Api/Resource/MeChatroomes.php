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
        $classRoomChatrooms = $this->getClassRoomChatrooms($user['id']);
        $courseChatrooms    = $this->getCourseChatrooms($user['id']);

        $chatrooms = array_merge($classRoomChatrooms, $courseChatrooms);
        return $this->wrap($this->filter($chatrooms), count($chatrooms));
    }

    private function getClassRoomChatrooms($userId)
    {
        $conditions = array('userId' => $userId);
        $total      = $this->getClassroomService()->searchMemberCount($conditions);
        $members    = $this->getClassroomService()->searchMembers($conditions, array('createdTime', 'DESC'), 0, $total);

        $classroomIds = ArrayToolkit::column($members, 'classroomId');

        $classrooms = $this->getClassroomService()->findClassroomsByIds($classroomIds);

        $chatrooms = array();
        foreach ($classrooms as $classroom) {
            $chatrooms[] = array(
                'type'    => 'classroom',
                'id'      => $classroom['id'],
                'title'   => $classroom['title'],
                'picture' => $this->getFileUrl($classroom['smallPicture'])
            );
        }

        return $chatrooms;
    }

    private function getCourseChatrooms($userId)
    {
        $conditions = array('userId' => $userId);
        $total      = $this->getCourseService()->searchMemberCount($conditions);
        $members    = $this->getCourseService()->searchMembers($conditions, array('createdTime', 'DESC'), 0, $total);

        $courseIds = ArrayToolkit::column($members, 'courseId');
        $courses   = $this->getCourseService()->findCoursesByIds($courseIds);
        $chatrooms = array();
        foreach ($courses as $course) {
            if ($course['parentId'] != 0) {
                continue;
            }
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
}
