<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\Task\Service\TaskService;

class MeJoined extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\CourseSet\CourseSetFilter", mode="simple")
     */
    public function search(ApiRequest $request)
    {
        //直播
        $allLiveCourseSets = $this->getCourseSetService()->searchCourseSets(
            ['status' => 'published', 'type' => 'live', 'parentId' => 0],
            ['createdTime' => 'DESC'],
            0,
            PHP_INT_MAX
        );

        $members = $this->getCourseMemberService()->searchMembers(
            ['courseSetIds' => array_column($allLiveCourseSets, 'id'), 'userId' => $this->getCurrentUser()->getId()],
            ['lastLearnTime' => 'DESC'],
            0,
            PHP_INT_MAX
        );

        $uniqueMemberIds = $this->getUniqueCourseSetIds($members);
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($uniqueMemberIds);

        foreach ($courseSets as &$courseSet) {
            $courseSet['meJoinedType'] = 'live';
        }

        //课程
        $conditions = $request->query->all();
        $conditions['status'] = 'published';
        $conditions['classroomId'] = 0;
        $conditions['joinedType'] = 'course';
        $conditions['userId'] = $this->getCurrentUser()->getId();
        $conditions['role'] = 'student';

        $members = $this->getCourseMemberService()->searchMembers(
            $conditions,
            ['lastLearnTime' => 'DESC'],
            0,
            PHP_INT_MAX
        );

        $courseConditions = [
            'ids' => ArrayToolkit::column($members, 'courseId') ?: [0],
            'excludeTypes' => ['reservation'],
        ];

        $courses = $this->getCourseService()->searchCourses(
            $courseConditions,
            [],
            0,
            PHP_INT_MAX
        );

        $courses = $this->appendAttrAndOrder($courses, $members);
        $courses = $this->getCourseService()->appendSpecsInfo($courses);
        $this->getOCUtil()->multiple($courses, ['courseSetId'], 'courseSet');
        foreach ($courses as &$course) {
            $course['meJoinedType'] = 'course';
        }
        //班级
        $querys = $request->query->all();

        $conditions = [
            'userId' => $this->getCurrentUser()->getId(),
            'role' => 'student',
        ];

        $total = $this->getClassroomService()->searchMemberCount($conditions);

        if (isset($querys['format']) && 'pagelist' == $querys['format']) {
            list($offset, $limit) = $this->getOffsetAndLimit($request);

            $classrooms = $this->getClassrooms($conditions, [], $offset, $limit);
            $classrooms = $this->getClassroomService()->appendSpecsInfo($classrooms);
        } else {
            $classrooms = $this->getClassrooms($conditions, [], 0, $total);
            $classrooms = $this->getClassroomService()->appendSpecsInfo($classrooms);
        }

        foreach ($classrooms as &$classroom) {
            $classroom['meJoinedType'] = 'classroom';
        }

        //题库
        $user = $this->getCurrentUser();
        $conditions = ['role' => 'student', 'userId' => $user['id']];
        $total = $this->getItemBankExerciseMemberService()->count($conditions);
        list($offset, $limit) = $this->getOffsetAndLimit($request);

        $members = $this->getItemBankExerciseMemberService()->search(
            $conditions,
            ['updatedTime' => 'DESC'],
            $offset,
            $limit
        );

        $itemBankExercises = $this->getItemBankExerciseService()->findByIds(ArrayToolkit::column($members, 'exerciseId'));
        foreach ($members as $key => &$member) {
            if (empty($itemBankExercises[$member['exerciseId']])) {
                unset($members[$key]);
            } else {
                $member['itemBankExercise'] = $itemBankExercises[$member['exerciseId']];
            }
            $member['meJoinedType'] = 'itemBankExercise';
        }

        return array_merge(array_values($this->orderByLastViewTime($courseSets, $uniqueMemberIds)), $courses, $classrooms, $members);
    }

    private function getClassrooms($conditions, $orderBy, $offset, $limit)
    {
        $classroomIds = ArrayToolkit::column(
            $this->getClassroomService()->searchMembers($conditions, [], $offset, $limit),
            'classroomId'
        );

        return array_values($this->getClassroomService()->findClassroomsByIds($classroomIds));
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
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

    private function getUniqueCourseSetIds($members)
    {
        return array_values(array_unique(array_column($members, 'courseSetId')));
    }

    private function orderByLastViewTime($courseSets, $uniqueCourseSetIds)
    {
        $orderedCourseSets = [];
        foreach ($uniqueCourseSetIds as $courseSetId) {
            if (!empty($courseSets[$courseSetId])) {
                $orderedCourseSets[] = $courseSets[$courseSetId];
            }
        }

        return $orderedCourseSets;
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ExerciseMemberService
     */
    protected function getItemBankExerciseMemberService()
    {
        return $this->service('ItemBankExercise:ExerciseMemberService');
    }

    /**
     * @return MemberService
     */
    private function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return CourseSetService
     */
    private function getCourseSetService()
    {
        return $this->service('Course:CourseSetService');
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
