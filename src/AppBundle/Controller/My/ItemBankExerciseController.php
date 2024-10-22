<?php

namespace AppBundle\Controller\My;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\ItemBankExercise\Service\AssessmentExerciseService;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\QuestionBank\Service\QuestionBankService;
use Symfony\Component\HttpFoundation\Request;

class ItemBankExerciseController extends BaseController
{
    public function teachingAction(Request $request, $filter)
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', 'my.teaching.view.forbidden');
        }

        $members = $this->getExerciseMemberService()->findByUserIdAndRole($user['id'], 'teacher');
        $conditions = [
            'ids' => ArrayToolkit::column($members, 'exerciseId'),
        ];

        $paginator = new Paginator(
            $request,
            !empty($members) ? $this->getItemBankExerciseService()->count($conditions) : 0,
            10
        );

        $itemBankExercises = [];
        if (!empty($members)) {
            $itemBankExercises = $this->getItemBankExerciseService()->search(
                $conditions,
                ['createdTime' => 'DESC'],
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );

            $exerciseIds = ArrayToolkit::column($itemBankExercises, 'id');
            $exerciseAssessments = $this->getAssessmentExerciseService()->getAssessmentCountGroupByExerciseId($exerciseIds);
            $exerciseAssessments = ArrayToolkit::index($exerciseAssessments, 'exerciseId');
            $questionBanks = ArrayToolkit::column($itemBankExercises, 'questionBankId');
            $questionBanks = $this->getQuestionBankService()->findQuestionBanksByIds($questionBanks);
            $questionBanks = ArrayToolkit::index($questionBanks, 'id');
            foreach ($itemBankExercises as &$itemBankExercise) {
                $itemBankExercise['assessmentNum'] = isset($exerciseAssessments[$itemBankExercise['id']]) ? $exerciseAssessments[$itemBankExercise['id']]['assessmentNum'] : 0;
                $itemBankExercise['itemNum'] = $questionBanks[$itemBankExercise['questionBankId']]['itemBank']['item_num'];
            }
        }

        return $this->render('my/teaching/item-bank-exercise.html.twig', [
            'itemBankExercises' => $itemBankExercises,
            'paginator' => $paginator,
            'filter' => $filter,
        ]);
    }

    public function itemBankAction(Request $request)
    {
        $currentUser = $this->getUser();

        $conditions = ['role' => 'student', 'userId' => $currentUser['id']];
        $paginator = new Paginator(
            $request,
            $this->getExerciseMemberService()->count($conditions),
            10
        );

        $members = $this->getExerciseMemberService()->search(
            $conditions,
            ['updatedTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $itemBankExercises = $this->getItemBankExerciseService()->findByIds(ArrayToolkit::column($members, 'exerciseId'));
        $exerciseAutoJoinRecords = $this->getItemBankExerciseService()->findExerciseAutoJoinRecordByUserIdAndExerciseIds($currentUser['id'], array_column($itemBankExercises, 'id'));
        $exerciseAutoJoinRecords = array_column($exerciseAutoJoinRecords, 'itemBankExerciseBindId');
        $exerciseBinds = $this->getItemBankExerciseService()->findBindExerciseByIds(array_column($exerciseAutoJoinRecords, 'itemBankExerciseBindId'));

        $courseIds = [];
        $classroomIds = [];

        foreach ($exerciseBinds as $exerciseBind) {
            if ('course' == $exerciseBind['type']) {
                $courseIds[] = $exerciseBind['bindId'];
            } else {
                $classroomIds[] = $exerciseBind['bindId'];
            }
        }

        // 批量获取课程和课堂数据
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $classrooms = $this->getClassroomService()->findClassroomsByIds($classroomIds);

        // 将课程和课堂的 title 映射为 [id => title] 的数组
        $courseTitles = array_column($courses, 'title', 'id');
        $classroomTitles = array_column($classrooms, 'title', 'id');

        // 将 title 赋值到 $exerciseBinds 中
        foreach ($exerciseBinds as &$exerciseBind) {
            if ('course' == $exerciseBind['type']) {
                $exerciseBind['bindTitle'] = '《'.$courseTitles[$exerciseBind['bindId']].'》、';
            } else {
                $exerciseBind['bindTitle'] = '《'.$classroomTitles[$exerciseBind['bindId']].'》、';
            }
        }

        foreach ($members as $key => &$member) {
            if (empty($itemBankExercises[$member['exerciseId']])) {
                unset($members[$key]);
            }
        }

        return $this->render(
            'my/learning/question-bank/list.html.twig',
            [
                'exercises' => ArrayToolkit::index($itemBankExercises, 'id'),
                'paginator' => $paginator,
                'members' => array_values($members),
            ]
        );
    }

    public function showAction(Request $request, $id, $tab = 'reviews', $moduleId = 0)
    {
        $user = $this->getCurrentUser();
        $exercise = $this->getItemBankExerciseService()->get($id);
        if (empty($exercise)) {
            $this->createNewException(ItemBankExerciseException::NOTFOUND_EXERCISE());
        }

        $member = $user['id'] ? $this->getExerciseMemberService()->getExerciseMember($exercise['id'], $user['id']) : null;

        if (empty($member) || ('date' == $exercise['expiryMode'] && $exercise['expiryStartDate'] >= time())) {
            return $this->redirectToRoute('item_bank_exercise_show', ['id' => $id]);
        }

        $tabs = $this->getTabs($exercise);
        if (!empty($tabs) && '' == $tab) {
            $tab = $tabs[0]['type'];
            $moduleId = $tabs[0]['id'];
        }

        return $this->render(
            'item-bank-exercise/my/my-exercise-show.html.twig',
            [
                'tab' => '' == $tab ? 'reviews' : $tab,
                'tabs' => $tabs,
                'member' => $member,
                'moduleId' => $moduleId,
                'isExerciseTeacher' => 'teacher' == $member['role'],
                'exercise' => $exercise,
                'previewAs' => 'member',
            ]
        );
    }

    protected function getTabs($exercise)
    {
        $condition['exerciseId'] = $exercise['id'];
        if ($exercise['chapterEnable']) {
            $condition['types'][] = 'chapter';
        }
        if ($exercise['assessmentEnable']) {
            $condition['types'][] = 'assessment';
        }

        return $this->getExerciseModuleService()->search(
            $condition,
            [],
            0,
            6
        );
    }

    public function headerAction(Request $request, $exercise)
    {
        $user = $this->getCurrentUser();

        $member = $user->isLogin() ? $this->getExerciseMemberService()->getExerciseMember($exercise['id'], $user['id']) : [];

        return $this->render(
            'item-bank-exercise/header/header-for-member.html.twig',
            [
                'member' => $member,
                'exercise' => $exercise,
                'previewAs' => $request->query->get('previewAs', 'member'),
            ]
        );
    }

    /**
     * @return ExerciseMemberService
     */
    protected function getExerciseMemberService()
    {
        return $this->createService('ItemBankExercise:ExerciseMemberService');
    }

    /**
     * @return ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->createService('ItemBankExercise:ExerciseService');
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->createService('QuestionBank:QuestionBankService');
    }

    /**
     * @return ExerciseModuleService
     */
    protected function getExerciseModuleService()
    {
        return $this->createService('ItemBankExercise:ExerciseModuleService');
    }

    /**
     * @return AssessmentExerciseService
     */
    protected function getAssessmentExerciseService()
    {
        return $this->createService('ItemBankExercise:AssessmentExerciseService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}
