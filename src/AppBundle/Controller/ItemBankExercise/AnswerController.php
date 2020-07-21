<?php

namespace AppBundle\Controller\ItemBankExercise;

use AppBundle\Controller\BaseController;
use Biz\Accessor\AccessorInterface;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Symfony\Component\HttpFoundation\Request;

class AnswerController extends BaseController
{
    public function assessmentAnswerAction(Request $request, $exerciseId, $moduleId, $assessmentId)
    {
        $user = $this->getCurrentUser();

        $access = $this->getItemBankExerciseService()->canLearnExercise($exerciseId);
        if (AccessorInterface::SUCCESS != $access['code']) {
            $this->createNewException(ItemBankExerciseException::FORBIDDEN_LEARN());
        }

        $latestAnswerRecord = $this->getItemBankAssessmentExerciseRecordService()->getLatestRecord($moduleId, $assessmentId, $user['id']);
        if (empty($latestAnswerRecord) || 'redo' == $request->get('action')) {
            $latestAnswerRecord = $this->getItemBankAssessmentExerciseService()->startAnswer($moduleId, $assessmentId, $user['id']);
        }

        if (AnswerService::ANSWER_RECORD_STATUS_REVIEWING == $latestAnswerRecord['status']) {
            return $this->forward('AppBundle:AnswerEngine/AnswerEngine:reviewAnswer', [
                'answerRecordId' => $latestAnswerRecord['answerRecordId'],
                'successGotoUrl' => $this->generateUrl('my_item_bank_exercise_show', ['id' => $exerciseId, 'moduleId' => $moduleId, 'tab' => 'chapter']),
            ]);
        } elseif (AnswerService::ANSWER_RECORD_STATUS_FINISHED == $latestAnswerRecord['status']) {
            return $this->render(
                'item-bank-exercise/answer/report.html.twig',
                [
                    'answerRecordId' => $latestAnswerRecord['answerRecordId'],
                    'restartUrl' => $this->generateUrl('item_bank_exercise_assessment_answer', ['exerciseId' => $exerciseId, 'moduleId' => $moduleId, 'assessmentId' => $assessmentId, 'action' => 'redo']),
                ]
            );
        } else {
            $this->getAnswerService()->continueAnswer($latestAnswerRecord['answerRecordId']);

            return $this->forward('AppBundle:AnswerEngine/AnswerEngine:do', [
                'answerRecordId' => $latestAnswerRecord['answerRecordId'],
                'submitGotoUrl' => $this->generateUrl('item_bank_exercise_assessment_answer', ['exerciseId' => $exerciseId, 'moduleId' => $moduleId, 'assessmentId' => $assessmentId]),
                'saveGotoUrl' => $this->generateUrl('my_item_bank_exercise_show', ['id' => $exerciseId, 'moduleId' => $moduleId, 'tab' => 'chapter']),
                'showHeader' => 1,
            ]);
        }
    }

    public function categoryAnswerAction(Request $request, $exerciseId, $moduleId, $categoryId)
    {
        $user = $this->getCurrentUser();

        $access = $this->getItemBankExerciseService()->canLearnExercise($exerciseId);
        if (AccessorInterface::SUCCESS != $access['code']) {
            $this->createNewException(ItemBankExerciseException::FORBIDDEN_LEARN());
        }

        $latestAnswerRecord = $this->getItemBankChapterExerciseRecordService()->getLatestRecord($moduleId, $categoryId, $user['id']);
        if (empty($latestAnswerRecord) || 'redo' == $request->get('action')) {
            $latestAnswerRecord = $this->getItemBankChapterExerciseService()->startAnswer($moduleId, $categoryId, $user['id']);
        }

        if (AnswerService::ANSWER_RECORD_STATUS_REVIEWING == $latestAnswerRecord['status']) {
            return $this->forward('AppBundle:AnswerEngine/AnswerEngine:reviewAnswer', [
                'answerRecordId' => $latestAnswerRecord['answerRecordId'],
                'successGotoUrl' => $this->generateUrl('my_item_bank_exercise_show', ['id' => $exerciseId, 'moduleId' => $moduleId, 'tab' => 'chapter']),
            ]);
        } elseif (AnswerService::ANSWER_RECORD_STATUS_FINISHED == $latestAnswerRecord['status']) {
            return $this->render(
                'item-bank-exercise/answer/report.html.twig',
                [
                    'answerRecordId' => $latestAnswerRecord['answerRecordId'],
                    'restartUrl' => $this->generateUrl('item_bank_exercise_category_answer', ['exerciseId' => $exerciseId, 'moduleId' => $moduleId, 'categoryId' => $categoryId, 'action' => 'redo']),
                ]
            );
        } else {
            $this->getAnswerService()->continueAnswer($latestAnswerRecord['answerRecordId']);

            return $this->forward('AppBundle:AnswerEngine/AnswerEngine:do', [
                'answerRecordId' => $latestAnswerRecord['answerRecordId'],
                'submitGotoUrl' => $this->generateUrl('item_bank_exercise_category_answer', ['exerciseId' => $exerciseId, 'moduleId' => $moduleId, 'categoryId' => $categoryId]),
                'saveGotoUrl' => $this->generateUrl('my_item_bank_exercise_show', ['id' => $exerciseId, 'moduleId' => $moduleId, 'tab' => 'chapter']),
                'showHeader' => 1,
            ]);
        }
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->createService('ItemBankExercise:ExerciseService');
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ChapterExerciseService
     */
    protected function getItemBankChapterExerciseService()
    {
        return $this->createService('ItemBankExercise:ChapterExerciseService');
    }

    /**
     * @return \Biz\ItemBankExercise\Service\AssessmentExerciseService
     */
    protected function getItemBankAssessmentExerciseService()
    {
        return $this->createService('ItemBankExercise:AssessmentExerciseService');
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ChapterExerciseRecordService
     */
    protected function getItemBankChapterExerciseRecordService()
    {
        return $this->createService('ItemBankExercise:ChapterExerciseRecordService');
    }

    /**
     * @return \Biz\ItemBankExercise\Service\AssessmentExerciseRecordService
     */
    protected function getItemBankAssessmentExerciseRecordService()
    {
        return $this->createService('ItemBankExercise:AssessmentExerciseRecordService');
    }

    /**
     * @return AnswerService
     */
    protected function getAnswerService()
    {
        return $this->createService('ItemBank:Answer:AnswerService');
    }
}
