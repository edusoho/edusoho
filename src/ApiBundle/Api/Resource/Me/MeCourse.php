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
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $members = $this->getCourseMemberService()->searchMembers(
            $conditions,
            ['lastLearnTime' => 'desc', 'createdTime' => 'desc'],
            0,
            PHP_INT_MAX
        );
        $validCourseIds = [];
        $invalidCourseIds = [];
        foreach ($members as &$member) {
            $member['lastLearnTime'] = (0 == $member['lastLearnTime']) ? $member['updatedTime'] : $member['lastLearnTime'];
            if ($this->isExpired($member['deadline'])) {
                $invalidCourseIds[] = $member['courseId'];
            } else {
                $validCourseIds[] = $member['courseId'];
            }
        }
        $courseConditions = [
            'ids' => array_merge($invalidCourseIds, $validCourseIds),
            'excludeTypes' => ['reservation'],
            'courseSetTitleLike' => $conditions['title'],
        ];
        if (empty($validCourseIds)) {
            return $this->makePagingObject([], 0, $offset, $limit);
        }
        $courses = $this->getCourseService()->searchCourses(['ids' => $validCourseIds, 'courseSetTitleLike' => $conditions['title']], [], 0, PHP_INT_MAX);
        $this->filterCourseIdsByConditions($conditions, $courses, $members, $validCourseIds, $invalidCourseIds, $courseConditions);
        if (isset($conditions['type']) && empty($courseConditions['ids'])) {
            return $this->makePagingObject([], 0, $offset, $limit);
        }
        $newCourseConditions = $courseConditions;
        $newCourseConditions['ids'] = array_slice($newCourseConditions['ids'], $offset, $limit);
        $courses = $this->getCourseService()->searchCourses(
            $newCourseConditions,
            [],
            0,
            $limit
        );

        $courses = $this->appendAttrAndOrder($courses, $members);
        $courses = $this->getCourseService()->appendSpecsInfo($courses);

        $total = $this->getCourseService()->countCourses($courseConditions);
        $this->getOCUtil()->multiple($courses, ['courseSetId'], 'courseSet');

        $membersIndex = ArrayToolkit::index($members, 'courseId');
        foreach ($courses as &$course) {
            if (isset($membersIndex[$course['id']])) {
                $course['lastLearnTime'] = $membersIndex[$course['id']]['lastLearnTime'];
                $course['isExpired'] = $this->isExpired($membersIndex[$course['id']]['deadline']);
            }
        }

        $courses = ArrayToolkit::index($courses, 'id');
        $courses = array_intersect_key($courses, array_flip(array_column($members, 'courseId')));
        $courses = array_values($courses);

        return $this->makePagingObject($courses, $total, $offset, $limit);
    }

    private function buildSearchConditions($request)
    {
        $conditions = $request->query->all();
        $conditions['canLearn'] = '1';
        $conditions['classroomId'] = 0;
        $conditions['joinedType'] = 'course';
        $conditions['userId'] = $this->getCurrentUser()->getId();
        $conditions['role'] = 'student';

        return $conditions;
    }

    private function filterCourseIdsByConditions($conditions, $courses, $members, $validCourseIds, $invalidCourseIds, &$courseConditions)
    {
        if (!empty($conditions['type'])) {
            switch ($conditions['type']) {
                case 'learning':
                case 'learned':
                    list($learnedCourseIds, $learningCourseIds) = $this->differentiateCourseSetIds($courses, $members);
                    $courseConditions['canLearn'] = '1';
                    unset($courseConditions['ids']);
                    $courseConditions['ids'] = ('learning' === $conditions['type']) ? $learningCourseIds : $learnedCourseIds;
                    break;
                case 'expired':
                    $closedCourses = $this->getCourseService()->searchCourses(['canLearn' => '0', 'ids' => $validCourseIds], [], 0, PHP_INT_MAX);
                    $courses = $this->getCourseService()->findCoursesByIds($invalidCourseIds);
                    $mergedCourses = array_merge($courses, $closedCourses);
                    $courses = array_unique($mergedCourses, SORT_REGULAR);
                    $courseConditions['ids'] = array_column($courses, 'id');
                    break;
            }
        }
    }

    private function isExpired($deadline)
    {
        return 0 != $deadline && $deadline < time();
    }

    protected function differentiateCourseSetIds($courses, $members)
    {
        if (empty($courses)) {
            return [[], []];
        }
        $courses = ArrayToolkit::index($courses, 'id');
        $learnedCourseIds = [];
        $learningCourseIds = [];
        foreach ($members as $member) {
            if (empty($courses[$member['courseId']])) {
                continue;
            }
            $course = $courses[$member['courseId']];
            $isLearned = 1;
            if ($member['learnedCompulsoryTaskNum'] < $course['compulsoryTaskNum'] or 0 == $course['compulsoryTaskNum']) {
                $isLearned = 0;
            }
            if ($isLearned) {
                array_push($learnedCourseIds, $course['id']);
            } else {
                array_push($learningCourseIds, $course['id']);
            }
        }

        return [$learnedCourseIds, $learningCourseIds];
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
