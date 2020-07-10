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
            'answer.started' => 'onAnswerStarted',
            'answer.submitted' => 'onAnswerSubmitted',
            'answer.finished' => 'onAnswerFinished',
        ];
    }

    public function onAnswerStarted(Event $event)
    {
        $answerRecord = $event->getSubject();
        $assessmentExerciseRecord = $this->getItemBankAssessmentExerciseRecordService()->getByAnswerRecordId($answerRecord['id']);
        if (empty($assessmentExerciseRecord)) {
            return;
        }

        return $this->getUserFootprintService()->createUserFootprint([
            'targetType' => 'item_bank_assessment_exercise',
            'targetId' => $assessmentExerciseRecord['id'],
            'event' => 'answer.started',
            'userId' => $assessmentExerciseRecord['userId'],
        ]);
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
    }

    /**
     * @return \Biz\ItemBankExercise\Service\AssessmentExerciseRecordService
     */
    protected function getItemBankAssessmentExerciseRecordService()
    {
        return $this->getBiz()->service('ItemBankExercise:AssessmentExerciseRecordService');
    }

    /**
     * @return \Biz\User\UserFootprintService
     */
    protected function getUserFootprintService()
    {
        return $this->getBiz()->service('User:UserFootprintService');
    }
}
