<?php

namespace Biz\ItemBankExercise\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ChapterExerciseEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'answer.submitted' => 'onAnswerSubmitted',
            'answer.saved' => 'onAnswerSaved',
            'answer.finished' => 'onAnswerFinished',
        ];
    }

    public function onAnswerSubmitted(Event $event)
    {
        $answerRecord = $event->getSubject();
        $chapterExerciseRecord = $this->getItemBankChapterExerciseRecordService()->getByAnswerRecordId($answerRecord['id']);
        if (empty($chapterExerciseRecord)) {
            return;
        }

        if (AnswerService::ANSWER_RECORD_STATUS_FINISHED == $answerRecord['status']) {
            $this->finished($chapterExerciseRecord['id'], $answerRecord['answer_report_id']);
        } else {
            $this->getItemBankChapterExerciseRecordService()->update(
                $chapterExerciseRecord['id'],
                [
                    'doneQuestionNum' => $this->getDoneQuestionNumByAnswerRecordId($answerRecord['id']),
                    'status' => $answerRecord['status']
                ]
            );
        }
    }

    public function onAnswerSaved(Event $event)
    {
        $assessmentResponse = $event->getSubject();
        $chapterExerciseRecord = $this->getItemBankChapterExerciseRecordService()->getByAnswerRecordId($assessmentResponse['answer_record_id']);
        if (empty($chapterExerciseRecord)) {
            return;
        }

        $this->getItemBankChapterExerciseRecordService()->update(
            $chapterExerciseRecord['id'],
            ['doneQuestionNum' => $this->getDoneQuestionNumByAssessmentResponse($assessmentResponse)]
        );
    }

    public function onAnswerFinished(Event $event)
    {
        $answerReport = $event->getSubject();
        $chapterExerciseRecord = $this->getItemBankChapterExerciseRecordService()->getByAnswerRecordId($answerReport['answer_record_id']);
        if (empty($chapterExerciseRecord)) {
            return;
        }

        $this->finished($chapterExerciseRecord['id'], $answerReport['id']);
    }

    protected function finished($chapterExerciseRecordId, $answerReportId)
    {
        $answerReport = $this->getAnswerReportService()->get($answerReportId);
        $this->getItemBankChapterExerciseRecordService()->update(
            $chapterExerciseRecordId,
            [
                'doneQuestionNum' => $this->getDoneQuestionNumByAnswerRecordId($answerReport['answer_record_id']),
                'rightQuestionNum' => $answerReport['right_question_count'],
                'rightRate' => $answerReport['right_rate'],
                'status' => AnswerService::ANSWER_RECORD_STATUS_FINISHED
            ]
        );
    }

    protected function getDoneQuestionNumByAssessmentResponse($assessmentResponse)
    {
        $doneQuestionNum = 0;

        foreach ($assessmentResponse['section_responses'] as $sectionResponse) {
            foreach ($sectionResponse['item_responses'] as $itemResponse) {
                foreach ($itemResponse['question_responses'] as $questionResponse) {
                    if (array_filter($questionResponse['response'])) {
                        ++$doneQuestionNum;
                    }
                }
            }
        }

        return $doneQuestionNum;
    }

    protected function getDoneQuestionNumByAnswerRecordId($answerRecordId)
    {
        $doneQuestionNum = 0;

        $answerQuestionReports = $this->getAnswerQuestionReportService()->findByAnswerRecordId($answerRecordId);
        foreach ($answerQuestionReports as $answerQuestionReport) {
            if (AnswerQuestionReportService::STATUS_NOANSWER != $answerQuestionReport['status']) {
                ++$doneQuestionNum;
            }
        }

        return $doneQuestionNum;
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ChapterExerciseRecordService
     */
    protected function getItemBankChapterExerciseRecordService()
    {
        return $this->getBiz()->service('ItemBankExercise:ChapterExerciseRecordService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerReportService
     */
    protected function getAnswerReportService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerReportService');
    }

    /**
     * @return AnswerQuestionReportService
     */
    protected function getAnswerQuestionReportService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerQuestionReportService');
    }
}
