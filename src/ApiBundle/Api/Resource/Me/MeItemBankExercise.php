<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;

class MeItemBankExercise extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $user = $this->getCurrentUser();
        $conditions = ['role' => 'student', 'userId' => $user['id'], 'canLearn' => '1'];
        $total = $this->getItemBankExerciseMemberService()->count($conditions);
        list($offset, $limit) = $this->getOffsetAndLimit($request);

        $members = $this->getItemBankExerciseMemberService()->search(
            $conditions,
            ['updatedTime' => 'DESC'],
            $offset,
            $limit
        );
        $itemBankExercises = $this->getItemBankExerciseService()->findByIds(ArrayToolkit::column($members, 'exerciseId'));
        $bindTitles = $this->findBindExerciseTitle($members);
        foreach ($members as $key => &$member) {
            if (empty($itemBankExercises[$member['exerciseId']])) {
                unset($members[$key]);
            } else {
                $member['itemBankExercise'] = $itemBankExercises[$member['exerciseId']];
                $member['isExpired'] = $this->isExpired($members['deadline']);
                $member['bindTitle'] = mb_substr($bindTitles[$member['exerciseId']], 0, mb_strlen($bindTitles[$member['exerciseId']], 'UTF-8') - 1);
            }
        }

        return $this->makePagingObject(array_values($members), $total, $offset, $limit);
    }

    private function isExpired($deadline)
    {
        return 0 != $deadline && $deadline < time();
    }

    protected function findBindExerciseTitle($members)
    {
        $exerciseAutoJoinRecords = $this->getItemBankExerciseService()->findExerciseAutoJoinRecordByUserIdAndExerciseIds($this->getCurrentUser()->getId(), array_column($members, 'exerciseId'));
        $exerciseBinds = $this->getItemBankExerciseService()->findBindExerciseByIds(array_column($exerciseAutoJoinRecords, 'itemBankExerciseBindId'));
        $courseIds = [];
        $classroomIds = [];
        foreach ($exerciseBinds as $exerciseBind) {
            if ('course' == $exerciseBind['bindType']) {
                $courseIds[] = $exerciseBind['bindId'];
            } else {
                $classroomIds[] = $exerciseBind['bindId'];
            }
        }
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $classrooms = $this->getClassroomService()->findClassroomsByIds($classroomIds);

        foreach ($courses as $course) {
            $courseTitles[$course['id']] = $course['courseSetTitle'];
        }

        foreach ($classrooms as $classroom) {
            $classroomTitles[$classroom['id']] = $classroom['title'];
        }
        $bindTitles = [];
        foreach ($exerciseBinds as $exerciseBind) {
            if ('course' == $exerciseBind['bindType'] && !empty($courseTitles[$exerciseBind['bindId']])) {
                $bindTitles[$exerciseBind['itemBankExerciseId']] .= '《'.$courseTitles[$exerciseBind['bindId']].'》、';
            } elseif ('classroom' == $exerciseBind['bindType'] && !empty($classroomTitles[$exerciseBind['bindId']])) {
                $bindTitles[$exerciseBind['itemBankExerciseId']] .= '《'.$classroomTitles[$exerciseBind['bindId']].'》、';
            }
        }

        return $bindTitles;
    }

    protected function getUserService()
    {
        return $this->service('User:UserService');
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
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }
}
