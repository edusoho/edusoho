<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Task\Service\TaskService;

class MeCourse extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        $conditions['status'] = 'published';
        $conditions['classroomId'] = 0;
        $conditions['joinedType'] = 'course';
        $conditions['userId'] = $this->getCurrentUser()->getId();
        $conditions['role'] = 'student';

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $members = $this->getCourseMemberService()->searchMembers(
            $conditions,
            ['lastLearnTime' => 'DESC'],
            0,
            PHP_INT_MAX
        );

        foreach ($members as &$member) {
            $member['lastLearnTime'] = (0 == $member['lastLearnTime']) ? $member['updatedTime'] : $member['lastLearnTime'];
        }
        array_multisort(ArrayToolkit::column($members, 'lastLearnTime'), SORT_DESC, $members);

        $courseConditions = [
            'ids' => ArrayToolkit::column($members, 'courseId') ?: [0],
            'excludeTypes' => ['reservation'],
        ];

        $courses = $this->getCourseService()->searchCourses(
            $courseConditions,
            [],
            $offset,
            $limit
        );

        $courses = $this->appendAttrAndOrder($courses, $members);
        $courses = $this->getCourseService()->appendSpecsInfo($courses);

        $total = $this->getCourseService()->countCourses($courseConditions);

        $this->getOCUtil()->multiple($courses, ['courseSetId'], 'courseSet');

        $members = ArrayToolkit::index($members, 'courseId');
        foreach ($courses as &$course) {
            if (isset($members[$course['id']])) {
                $course['lastLearnTime'] = $members[$course['id']]['lastLearnTime'];
            }
        }
        array_multisort(ArrayToolkit::column($courses, 'lastLearnTime'), SORT_DESC, $courses);

        return $this->makePagingObject($courses, $total, $offset, $limit);
    }

    private function appendAttrAndOrder($courses, $members)
    {
        $orderedCourses = [];
        $members = ArrayToolkit::index($members, 'courseId');
        $courses = ArrayToolkit::index($courses, 'id');
        foreach ($members as $member) {
            $courseId = $member['courseId'];
            if (!empty($courses[$courseId])) {
                $course = $courses[$courseId];
                $course['learnedNum'] = $member['learnedNum'];
                $course['learnedCompulsoryTaskNum'] = $member['learnedCompulsoryTaskNum'];
                /*
                 * @TODO 2017-06-29 业务变更、字段变更:publishedTaskNum变更为compulsoryTaskNum,兼容一段时间
                 */
                $course['publishedTaskNum'] = $course['compulsoryTaskNum'];
                $course['progress'] = $this->getLearningDataAnalysisService()->makeProgress($course['learnedCompulsoryTaskNum'], $course['compulsoryTaskNum']);
                $orderedCourses[] = $course;
            }
        }

        return $orderedCourses;
    }

    /**
     * @return MemberService
     */
    private function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    private function getTaskService()
    {
        return $this->service('Task:TaskService');
    }

    private function getLearningDataAnalysisService()
    {
        return $this->service('Course:LearningDataAnalysisService');
    }
}
