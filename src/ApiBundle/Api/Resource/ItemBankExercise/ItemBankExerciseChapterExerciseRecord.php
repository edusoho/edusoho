<?php

namespace ApiBundle\Api\Resource\ItemBankExercise;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\Assessment\AssessmentFilter;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\ItemBankExercise\Service\ChapterExerciseRecordService;
use Codeages\Biz\ItemBank\Answer\Constant\ExerciseMode;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;

class ItemBankExerciseChapterExerciseRecord extends AbstractResource
{
    public function add(ApiRequest $request, $exerciseId)
    {
        $user = $this->getCurrentUser();
        $moduleId = $request->request->get('moduleId', '');
        $categoryId = $request->request->get('categoryId', '');

        $latestRecord = $this->getItemBankChapterExerciseRecordService()->getLatestRecord($moduleId, $categoryId, $user['id']);
        if (!empty($latestRecord) && AnswerService::ANSWER_RECORD_STATUS_FINISHED != $latestRecord['status']) {
            throw ItemBankExerciseException::CHAPTER_ANSWER_IS_DOING();
        }

        $chapterExerciseRecord = $this->getItemBankChapterExerciseService()->startAnswer(
            $moduleId,
            $categoryId,
            $user['id'],
            $request->request->get('exerciseMode', ExerciseMode::SUBMIT_ALL)
        );
        $answerRecord = $this->getAnswerRecordService()->get($chapterExerciseRecord['answerRecordId']);

        $assessment = $this->getAssessmentService()->showAssessment($answerRecord['assessment_id']);
        $assessmentFilter = new AssessmentFilter();
        $assessmentFilter->filter($assessment);

        return [
            'assessment' => $assessment,
            'assessment_response' => $this->getAnswerService()->getAssessmentResponseByAnswerRecordId($answerRecord['id']),
            'answer_scene' => $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']),
            'answer_record' => $answerRecord,
        ];
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ChapterExerciseService
     */
    protected function getItemBankChapterExerciseService()
    {
        return $this->service('ItemBankExercise:ChapterExerciseService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
     */
    protected function getAnswerSceneService()
    {
        return $this->service('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerService;
     */
    protected function getAnswerService()
    {
        return $this->service('ItemBank:Answer:AnswerService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
     */
    protected function getAssessmentService()
    {
        return $this->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return ChapterExerciseRecordService
     */
    protected function getItemBankChapterExerciseRecordService()
    {
        return $this->service('ItemBankExercise:ChapterExerciseRecordService');
    }
}
