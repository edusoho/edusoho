<?php

namespace AppBundle\Controller\My;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
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
        $members = $this->getExerciseMemberService()->findByUserIdAndRole($currentUser['id'], 'student');
        $exerciseIds = ArrayToolkit::column($members, 'exerciseId');
        $conditions = ['ids' => $exerciseIds];

        $paginator = new Paginator(
            $request,
            !empty($members) ? $this->getItemBankExerciseService()->count($conditions) : 0,
            12
        );

        $exercises = [];
        if (!empty($exerciseIds)) {
            $exercises = $this->getItemBankExerciseService()->search(
                $conditions,
                [],
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
        }

        return $this->render(
            'my/learning/question-bank/list.html.twig',
            [
                'exercises' => ArrayToolkit::index($exercises, 'id'),
                'paginator' => $paginator,
                'members' => ArrayToolkit::index($members, 'exerciseId'),
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
            return $this->redirectToRoute('course_show', ['id' => $id]);
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
}
