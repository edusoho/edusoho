<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use AppBundle\Common\TimeMachine;

class Course extends AbstractResource
{
    const DEFAULT_PAGING_LIMIT = 30;

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (!$course) {
            throw CourseException::NOTFOUND_COURSE();
        }

        $user = $this->getCurrentUser();

        if ($user->isLogin()) {
            $member = $this->getMemberService()->getCourseMember($course['id'], $user['id']);
        }

        if ($course['parentId'] > 0) {
            $classroom = $this->getClassroomService()->getClassroomByCourseId($course['id']);
        }

        if (!empty($classroom) && empty($member)) {
            $this->joinCourseMemberByClassroomId($course['id'], $classroom['id']);
        }

        $this->getOCUtil()->single($course, array('creator', 'teacherIds'));
        $this->getOCUtil()->single($course, array('courseSetId'), 'courseSet');

        if (!empty($member)) {
            $course['access'] = $this->getCourseService()->canLearnCourse($courseId);
        } else {
            $course['access'] = $this->getCourseService()->canJoinCourse($courseId);
        }

        $course = $this->convertFields($course);

        return $course;
    }

    protected function convertFields($course)
    {
        $enableAudioStatus = $this->getCourseService()->isSupportEnableAudio($course['enableAudio']);
        $course['isAudioOn'] = $enableAudioStatus ? '1' : '0';
        unset($course['enableAudio']);

        return $course;
    }

    protected function joinCourseMemberByClassroomId($courseId, $classroomId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        $user = $this->getCurrentUser();

        $classroomMember = $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']);

        if (empty($classroomMember) || !in_array('student', $classroomMember['role'])) {
            return;
        }

        $info = array(
            'levelId' => empty($classroomMember['levelId']) ? 0 : $classroomMember['levelId'],
            'deadline' => $classroomMember['deadline'],
        );

        $this->getMemberService()->createMemberByClassroomJoined($courseId, $user['id'], $classroom['id'], $info);
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        if (isset($conditions['type']) && 'all' == $conditions['type']) {
            unset($conditions['type']);
        }

        $conditions['status'] = 'published';
        $conditions['courseSetStatus'] = 'published';
        $conditions['parentId'] = isset($conditions['parentId']) ? $conditions['parentId'] : 0;
        //过滤约排课
        $conditions['excludeTypes'] = array('reservation');
        if (!empty($conditions['lastDays'])) {
            $timeRange = TimeMachine::getTimeRangeByDays($conditions['lastDays']);
            $conditions['outerStartTime'] = $timeRange['startTime'];
            $conditions['outerEndTime'] = $timeRange['endTime'];
        }

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $sort = $this->getSort($request);

        $courses = $this->getCourseService()->searchBySort($conditions, $sort, $offset, $limit);
        $total = $this->getCourseService()->countWithJoinCourseSet($conditions);

        $this->getOCUtil()->multiple($courses, array('creator', 'teacherIds'));
        $this->getOCUtil()->multiple($courses, array('courseSetId'), 'courseSet');

        return $this->makePagingObject($courses, $total, $offset, $limit);
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->service('Course:MemberService');
    }
}
