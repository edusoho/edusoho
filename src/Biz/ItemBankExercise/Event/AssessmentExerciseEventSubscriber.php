<?php

namespace Biz\ItemBankExercise\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AssessmentExerciseEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'answer.submitted' => 'onAnswerSubmitted',
            'answer.finished' => 'onAnswerFinished',
        ];
    }

    public function onAnswerSubmitted(Event $event)
    {
        $answerRecord = $event->getSubject();
        $assessmentExerciseRecord = $this->getItemBankAssessmentExerciseRecordService()->getByAnswerRecordId($answerRecord['id']);
        if (empty($assessmentExerciseRecord)) {
            return;
        }

        $this->getItemBankAssessmentExerciseRecordService()->update(
            $assessmentExerciseRecord['id'],
            [
                'status' => $answerRecord['status'],
            ]
        );
    }

    public function onAnswerFinished(Event $event)
    {
        $answerReport = $event->getSubject();
        $assessmentExerciseRecord = $this->getItemBankAssessmentExerciseRecordService()->getByAnswerRecordId($answerReport['answer_record_id']);
        if (empty($assessmentExerciseRecord)) {
            return;
        }

        $this->getItemBankAssessmentExerciseRecordService()->update(
            $assessmentExerciseRecord['id'],
            [
                'status' => AnswerService::ANSWER_RECORD_STATUS_FINISHED,
            ]
        );

        $this->getItemBankExerciseQuestionRecordService()->updateByAnswerRecordIdAndModuleId($answerReport['answer_record_id'], $assessmentExerciseRecord['moduleId']);

        $this->getItemBankExerciseMemberService()->updateMasteryRate($assessmentExerciseRecord['exerciseId'], $assessmentExerciseRecord['userId']);
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ExerciseQuestionRecordService
     */
    protected function getItemBankExerciseQuestionRecordService()
    {
        return $this->getBiz()->service('ItemBankExercise:ExerciseQuestionRecordService');
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ExerciseMemberService
     */
    protected function getItemBankExerciseMemberService()
    {
        return $this->getBiz()->service('ItemBankExercise:ExerciseMemberService');
    }

    /**
     * @return \Biz\ItemBankExercise\Service\AssessmentExerciseRecordService
     */
    protected function getItemBankAssessmentExerciseRecordService()
    {
        return $this->getBiz()->service('ItemBankExercise:AssessmentExerciseRecordService');
    }
}
