<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Course\Service\CourseSetService;
use Biz\Classroom\Service\ClassroomService;
use ApiBundle\Api\Util\AssetHelper;
use Biz\Course\Util\CourseTitleUtils;

class MeChatRoom extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $user = $this->getCurrentUser();

        $classRoomChatrooms = $this->getClassRoomChatrooms($user['id']);

        $courseChatrooms = $this->getCourseChatrooms($user['id']);

        $chatrooms = array_merge($classRoomChatrooms, $courseChatrooms);

        return $chatrooms;
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
                'picture' => AssetHelper::getFurl($classroom['smallPicture'], 'classroom.png'),
            );
        }

        return $chatrooms;
    }

    private function getCourseChatrooms($userId)
    {
        $conditions = array('userId' => $userId);
        $members = $this->getCourseMemberService()->searchMembers(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            $this->getCourseMemberService()->countMembers($conditions)
        );

        $courseIds = ArrayToolkit::column($members, 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        $setIds = ArrayToolkit::column($courses, 'courseSetId');
        $sets = $this->getCourseSetService()->findCourseSetsByIds($setIds);
        $sets = ArrayToolkit::index($sets, 'id');

        $chatrooms = array();
        foreach ($courses as $course) {
            if (0 != $course['parentId']) {
                continue;
            }

            $set = $sets[$course['courseSetId']];
            $filePath = is_null($set['cover']) ? '' : ArrayToolkit::get($set['cover'], 'small', '');

            $chatrooms[] = array(
                'type' => 'course',
                'id' => $course['id'],
                'title' => CourseTitleUtils::getDisplayedTitle($course),
                'picture' => AssetHelper::getFurl($filePath, 'course.png'),
            );
        }

        return $chatrooms;
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->service('Course:CourseSetService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }
}
