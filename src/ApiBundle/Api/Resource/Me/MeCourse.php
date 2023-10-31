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
        $conditions = $this->buildSearchConditions($request);
        file_put_contents('/tmp/jc123', json_encode($conditions), 8);
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $members = $this->getCourseMemberService()->searchMembers(
            $conditions,
            ['lastLearnTime' => 'desc', 'createdTime' => 'desc'],
            0,
            PHP_INT_MAX
        );
        foreach ($members as &$member) {
            $member['lastLearnTime'] = (0 == $member['lastLearnTime']) ? $member['updatedTime'] : $member['lastLearnTime'];
        }
        array_multisort(ArrayToolkit::column($members, 'lastLearnTime'), SORT_DESC, $members);
        $courseIds = ArrayToolkit::column($members, 'courseId');
        $courseConditions = [
            'ids' => $courseIds,
            'excludeTypes' => ['reservation'],
            'courseSetTitleLike' => $conditions['title'],
        ];
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        if ('isExpired' === $conditions['learningStatus']) {
            $closedCourses = $this->getCourseService()->searchCourses(['status' => 'closed'], [], 0, PHP_INT_MAX);
            $mergedCourses = array_merge($courses, $closedCourses);
            $courses = array_unique($mergedCourses, SORT_REGULAR);
        }
        if (!empty($conditions['learningStatus'])) {
            if ('learning' === $conditions['learningStatus']) {
                $courses = ArrayToolkit::group($courses, 'courseSetId');
                list($learnedCourseSetIds, $learningCourseSetIds) = $this->differentiateCourseSetIds($courses, $members);
                $courseConditions['ids'] = $learningCourseSetIds;
                $courseConditions['status'] = 'published';
            }
            if ('learned' === $conditions['learningStatus']) {
                $courses = ArrayToolkit::group($courses, 'courseSetId');
                list($learnedCourseSetIds, $learningCourseSetIds) = $this->differentiateCourseSetIds($courses, $members);
                $courseConditions['ids'] = $learnedCourseSetIds;
                $courseConditions['status'] = 'published';
            }
            if ('isExpired' === $conditions['learningStatus']) {
                $courseConditions['ids'] = array_column($courses, 'id');
            }
        }

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
                $deadline = $members[$course['id']]['deadline'];
                $course['isExpired'] = 0 !== $deadline && $deadline > time();
            }
        }
        array_multisort(ArrayToolkit::column($courses, 'lastLearnTime'), SORT_DESC, $courses);

        return $this->makePagingObject($courses, $total, $offset, $limit);
    }

    private function buildSearchConditions($request)
    {
        $conditions = $request->query->all();
        $conditions['status'] = 'published';
        $conditions['classroomId'] = 0;
        $conditions['joinedType'] = 'course';
        $conditions['userId'] = $this->getCurrentUser()->getId();
        $conditions['role'] = 'student';
        if (!empty($conditions['learningStatus'])) {
            $this->applyLearningStatusConditions($conditions['learningStatus'], $conditions);
        }

        return $conditions;
    }

    private function applyLearningStatusConditions($learningStatus, &$conditions)
    {
        switch ($learningStatus) {
            case 'learning':
            case 'learned':
                $conditions['deadline'] = '0';
                $conditions['orDeadlineGreaterThan'] = time();
                break;
            case 'isExpired':
                $conditions['deadlineNotEqual'] = '0';
                $conditions['deadlineLessThen'] = time();
                break;
        }
    }

    protected function differentiateCourseSetIds($groupCourses, $members)
    {
        if (empty($groupCourses)) {
            return [[-1], [-1]];
        }
        $members = ArrayToolkit::index($members, 'courseId');
        $learnedCourseSetIds = [];
        $learningCourseSetIds = [];
        foreach ($groupCourses as $courseSetId => $courses) {
            $isLearned = 1;
            array_map(function ($course) use ($members, &$isLearned) {
                $member = $members[$course['id']];
                if ($member['learnedCompulsoryTaskNum'] < $course['compulsoryTaskNum'] or 0 == $course['compulsoryTaskNum']) {
                    $isLearned = 0;
                }
            }, $courses);

            if ($isLearned) {
                array_push($learnedCourseSetIds, $courseSetId);
            } else {
                array_push($learningCourseSetIds, $courseSetId);
            }
        }

        return [$learnedCourseSetIds ?: [-1], $learningCourseSetIds ?: [-1]];
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
