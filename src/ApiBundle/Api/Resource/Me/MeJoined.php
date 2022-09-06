<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Util\AssetHelper;
use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\Service\LearningDataAnalysisService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\Task\Service\TaskService;

class MeJoined extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        //直播
        $liveCourseSets = $this->getLiveCourseSets();

        //课程
        $courses = $this->getCourses();

        //班级
        $classrooms = $this->getClassrooms();

        //题库
        $itemBanks = $this->getItemBanks();

        $data = array_merge($liveCourseSets, $courses, $classrooms, $itemBanks);
        array_multisort(ArrayToolkit::column($data, 'lastLearnTime'), SORT_DESC, $data);

        return $data;
    }

    protected function getLiveCourseSets()
    {
        $allLiveCourseSets = $this->getCourseSetService()->searchCourseSets(
            ['status' => 'published', 'type' => 'live', 'parentId' => 0],
            ['createdTime' => 'DESC'],
            0,
            PHP_INT_MAX
        );
        $courseSetIds = empty(array_column($allLiveCourseSets, 'id')) ? [-1] : array_column($allLiveCourseSets, 'id');

        $members = $this->getCourseMemberService()->searchMembers(
            ['courseSetIds' => $courseSetIds, 'userId' => $this->getCurrentUser()->getId()],
            ['lastLearnTime' => 'DESC'],
            0,
            PHP_INT_MAX
        );

        $uniqueMemberIds = $this->getUniqueCourseSetIds($members);
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($uniqueMemberIds);

        foreach ($members as $member) {
            $courseSets[$member['courseSetId']]['lastLearnTime'] = (0 == $member['lastLearnTime']) ? $member['updatedTime'] : $member['lastLearnTime'];
            $courseSets[$member['courseSetId']]['meJoinedType'] = 'live';
            $courseSets[$member['courseSetId']]['cover'] = $this->transformCover($courseSets[$member['courseSetId']]['cover'], 'course');
        }

        return array_values($this->orderByLastViewTime($courseSets, $uniqueMemberIds));
    }

    protected function getCourses()
    {
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
        if (!empty($courseSets)) {
            $courseConditions['excludeCourseSetIds'] = ArrayToolkit::column($courseSets, 'id');
        }
        $courses = $this->getCourseService()->searchCourses(
            $courseConditions,
            [],
            0,
            PHP_INT_MAX
        );
        $courses = $this->appendAttrAndOrder($courses, $members);
        $courses = $this->getCourseService()->appendSpecsInfo($courses);
        $this->getOCUtil()->multiple($courses, ['courseSetId'], 'courseSet');
        $courses = ArrayToolkit::index($courses, 'id');
        foreach ($members as $member) {
            if (isset($courses[$member['courseId']])) {
                $courses[$member['courseId']]['lastLearnTime'] = (0 == $member['lastLearnTime']) ? $member['updatedTime'] : $member['lastLearnTime'];
                $courses[$member['courseId']]['meJoinedType'] = 'course';
                $courses[$member['courseId']]['courseSet']['cover'] = $this->transformCover($courses[$member['courseId']]['courseSet']['cover'], 'course');
                $courses[$member['courseId']]['cover'] = is_string($courses[$member['courseId']]['cover']) ? null : $courses[$member['courseId']]['cover'];
            }
        }

        return $courses;
    }

    protected function getClassrooms()
    {
        $conditions = [
            'userId' => $this->getCurrentUser()->getId(),
            'role' => 'student',
        ];

        $total = $this->getClassroomService()->searchMemberCount($conditions);

        $members = $this->getClassroomService()->searchMembers($conditions, [], 0, $total);
        $classroomIds = ArrayToolkit::column($members, 'classroomId');

        $classrooms = array_values($this->getClassroomService()->findClassroomsByIds($classroomIds));
        $classrooms = $this->getClassroomService()->appendSpecsInfo($classrooms);
        $classrooms = ArrayToolkit::index($classrooms, 'id');

        foreach ($members as $member) {
            $classrooms[$member['classroomId']]['meJoinedType'] = 'classroom';
            $classrooms[$member['classroomId']]['lastLearnTime'] = (0 == $member['lastLearnTime']) ? $member['updatedTime'] : $member['lastLearnTime'];
            $classrooms[$member['classroomId']]['smallPicture'] = AssetHelper::getFurl($classrooms[$member['classroomId']]['smallPicture'], 'classroom.png');
            $classrooms[$member['classroomId']]['middlePicture'] = AssetHelper::getFurl($classrooms[$member['classroomId']]['smallPicture'], 'classroom.png');
            $classrooms[$member['classroomId']]['largePicture'] = AssetHelper::getFurl($classrooms[$member['classroomId']]['smallPicture'], 'classroom.png');
            $progress = $this->getClassroomLearningDataAnalysisService()->getUserLearningProgress($member['classroomId'], $this->getCurrentUser()->getId());
            $classrooms[$member['classroomId']]['learningProgressPercent'] = $progress['percent'];
        }

        return $classrooms;
    }

    protected function getItemBanks()
    {
        $user = $this->getCurrentUser();
        $conditions = ['role' => 'student', 'userId' => $user['id']];
        $total = $this->getItemBankExerciseMemberService()->count($conditions);

        $members = $this->getItemBankExerciseMemberService()->search(
            $conditions,
            ['updatedTime' => 'DESC'],
            0,
            PHP_INT_MAX
        );

        $itemBankExercises = $this->getItemBankExerciseService()->findByIds(ArrayToolkit::column($members, 'exerciseId'));
        foreach ($members as $key => &$member) {
            if (empty($itemBankExercises[$member['exerciseId']])) {
                unset($members[$key]);
            } else {
                $member['itemBankExercise'] = $itemBankExercises[$member['exerciseId']];
            }
            $member['meJoinedType'] = 'itemBankExercise';
            $member['lastLearnTime'] = $member['updatedTime'];
            $member['itemBankExercise']['cover'] = $this->transformCover($member['itemBankExercise']['cover'], 'item_bank_exercise');
        }

        return $members;
    }

    private function transformCover($cover, $type)
    {
        $cover['small'] = AssetHelper::getFurl(empty($cover['small']) ? '' : $cover['small'], $type.'.png');
        $cover['middle'] = AssetHelper::getFurl(empty($cover['middle']) ? '' : $cover['middle'], $type.'.png');
        $cover['large'] = AssetHelper::getFurl(empty($cover['large']) ? '' : $cover['large'], $type.'.png');

        return $cover;
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

    /**
     * @return LearningDataAnalysisService
     */
    protected function getClassroomLearningDataAnalysisService()
    {
        return $this->service('Classroom:LearningDataAnalysisService');
    }
}
