<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Course\Service\CourseSetService;
use Biz\Classroom\Service\ClassroomService;
use Symfony\Component\HttpFoundation\Request;

class MeChatroomes extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 10);

        $user = $this->getCurrentUser();
        $classRoomChatrooms = $this->getClassRoomChatrooms($user['id']);
        $courseChatrooms = $this->getCourseChatrooms($user['id']);

        $chatrooms = array_merge($classRoomChatrooms, $courseChatrooms);

        return $this->wrap($this->filter($chatrooms), count($chatrooms));
    }

    private function getClassRoomChatrooms($userId)
    {
        $conditions = array('userId' => $userId);
        $total = $this->getClassroomService()->searchMemberCount($conditions);
        $members = $this->getClassroomService()->searchMembers($conditions, array('createdTime' => 'DESC'), 0, $total);

        $classroomIds = ArrayToolkit::column($members, 'classroomId');

        $classrooms = $this->getClassroomService()->findClassroomsByIds($classroomIds);

        $chatrooms = array();
        foreach ($classrooms as $classroom) {
            $chatrooms[] = array(
                'type' => 'classroom',
                'id' => $classroom['id'],
                'title' => $classroom['title'],
                'picture' => $this->getFileUrl($classroom['smallPicture'], 'classroom.png'),
            );
        }

        return $chatrooms;
    }

    private function getCourseChatrooms($userId)
    {
        $conditions = array('userId' => $userId);
        $total = $this->getCourseMemberService()->countMembers($conditions);
        $members = $this->getCourseMemberService()->searchMembers(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            $total
        );

        $courseIds = ArrayToolkit::column($members, 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $setIds = ArrayToolkit::column($courses, 'courseSetId');
        $sets = $this->getCourseSetService()->findCourseSetsByIds($setIds);
        $sets = ArrayToolkit::index($sets, 'id');
        $chatrooms = array();
        foreach ($courses as $course) {
            if ($course['parentId'] != 0) {
                continue;
            }

            $set = $sets[$course['courseSetId']];
            if ($course['courseType'] == CourseService::DEFAULT_COURSE_TYPE) {
                $title = $set['title'];
            } else {
                $title = $set['title'].'-'.$course['title'];
            }
            $filePath = is_null($set['cover']) ? '' : ArrayToolkit::get($set['cover'], 'small', '');
            $chatrooms[] = array(
                'type' => 'course',
                'id' => $course['id'],
                'title' => $title,
                'picture' => $this->getFileUrl($filePath, 'course.png'),
            );
        }

        return $chatrooms;
    }

    public function filter($res)
    {
        return $res;
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }
}
