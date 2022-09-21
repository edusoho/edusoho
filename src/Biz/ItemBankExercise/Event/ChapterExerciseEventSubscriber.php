<?php

namespace Biz\ItemBankExercise\Event;

use AppBundle\Common\ArrayToolkit;
use Biz\Crontab\SystemCrontabInitializer;
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
            'item.create' => 'onItemCreate',
            'item.update' => 'onItemUpdate',
            'item.delete' => 'onItemDelete',
            'item.import' => 'onItemImport',
            'item.batchDelete' => 'onItemBatchDelete',
        ];
    }

    public function onItemCreate(Event $event)
    {
        $item = $event->getSubject();
        if (!empty($item)) {
            $this->createUpdateMemberMasteryRateJob($item['bank_id']);
        }
    }

    public function onItemUpdate(Event $event)
    {
        $item = $event->getSubject();
        $item = $this->getItemService()->getItemWithQuestions($item['id']);
        $originItem = $event->getArgument('originItem');
        if (!empty($item)) {
            $originQuestionIds = ArrayToolkit::column($originItem['questions'], 'id');
            $itemQuestionIds = ArrayToolkit::column($item['questions'], 'id');
            $deleteQuestionIds = [];
            foreach ($originQuestionIds as $originQuestionId) {
                if (!in_array($originQuestionId, $itemQuestionIds)) {
                    $deleteQuestionIds[] = $originQuestionId;
                }
            }
            if ($deleteQuestionIds) {
                $this->getItemBankExerciseQuestionRecordService()->deleteByQuestionIds($deleteQuestionIds);
                $this->createUpdateMemberMasteryRateJob($item['bank_id']);
            }
        }
    }

    public function onItemDelete(Event $event)
    {
        $item = $event->getSubject();
        if (!empty($item)) {
            $this->getItemBankExerciseQuestionRecordService()->deleteByItemIds([$item['id']]);
            $this->createUpdateMemberMasteryRateJob($item['bank_id']);
        }
    }

    public function onItemImport(Event $event)
    {
        $items = $event->getSubject();
        if (!empty($items)) {
            $this->createUpdateMemberMasteryRateJob(current($items)['bank_id']);
        }
    }

    public function onItemBatchDelete(Event $event)
    {
        $items = $event->getSubject();
        if (!empty($items)) {
            $this->getItemBankExerciseQuestionRecordService()->deleteByItemIds(ArrayToolkit::column($items, 'id'));
            $this->createUpdateMemberMasteryRateJob(current($items)['bank_id']);
        }
    }

    public function onAnswerSubmitted(Event $event)
    {
        $answerRecord = $event->getSubject();
        $chapterExerciseRecord = $this->getItemBankChapterExerciseRecordService()->getByAnswerRecordId($answerRecord['id']);
        if (empty($chapterExerciseRecord)) {
            return;
        }

        if (AnswerService::ANSWER_RECORD_STATUS_FINISHED == $answerRecord['status']) {
            $this->finished($chapterExerciseRecord, $answerRecord['answer_report_id']);
        } else {
            $this->getItemBankChapterExerciseRecordService()->update(
                $chapterExerciseRecord['id'],
                [
                    'doneQuestionNum' => $this->getDoneQuestionNumByAnswerReport(
                        $this->getAnswerReportService()->get($answerRecord['answer_report_id'])
                    ),
                    'status' => $answerRecord['status'],
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

        $this->finished($chapterExerciseRecord, $answerReport['id']);
    }

    protected function createUpdateMemberMasteryRateJob($itemBankId)
    {
        $questionBank = $this->getQuestionBankService()->getQuestionBankByItemBankId($itemBankId);
        $itemBankExericse = $this->getItemBankExerciseService()->getByQuestionBankId($questionBank['id']);
        if (empty($itemBankExericse)) {
            return;
        }

        $job = $this->getSchedulerService()->getJobByName('UpdateItemBankMemberMasteryRateJob_'.$itemBankExericse['id']);
        if (empty($job)) {
            $this->getSchedulerService()->register([
                'name' => 'UpdateItemBankMemberMasteryRateJob_'.$itemBankExericse['id'],
                'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
                'expression' => intval(time() + 3 * 60),
                'misfire_policy' => 'executing',
                'class' => 'Biz\ItemBankExercise\Job\UpdateMemberMasteryRateJob',
                'args' => ['itemBankExericseId' => $itemBankExericse['id']],
            ]);
        }
    }

    protected function finished($chapterExerciseRecord, $answerReportId)
    {
        $answerReport = $this->getAnswerReportService()->get($answerReportId);

        $this->getItemBankChapterExerciseRecordService()->update(
            $chapterExerciseRecord['id'],
            [
                'doneQuestionNum' => $this->getDoneQuestionNumByAnswerReport($answerReport),
                'rightQuestionNum' => $answerReport['right_question_count'],
                'rightRate' => $answerReport['right_rate'],
                'status' => AnswerService::ANSWER_RECORD_STATUS_FINISHED,
            ]
        );

        $this->getItemBankExerciseQuestionRecordService()->updateByAnswerRecordIdAndModuleId($answerReport['answer_record_id'], $chapterExerciseRecord['moduleId']);

        $this->getItemBankExerciseMemberService()->updateMasteryRate($chapterExerciseRecord['exerciseId'], $chapterExerciseRecord['userId']);
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

    protected function getDoneQuestionNumByAnswerReport($answerReport)
    {
        $doneQuestionNum = 0;

        foreach ($answerReport['section_reports'] as $sectionReport) {
            foreach ($sectionReport['item_reports'] as $itemReport) {
                foreach ($itemReport['question_reports'] as $questionReport) {
                    if (!in_array($questionReport['status'], [AnswerQuestionReportService::STATUS_NOANSWER])) {
                        ++$doneQuestionNum;
                    }
                }
            }
        }

        return $doneQuestionNum;
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
     * @return \Biz\ItemBankExercise\Service\ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->getBiz()->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return \Biz\QuestionBank\Service\QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->getBiz()->service('QuestionBank:QuestionBankService');
    }

    /**
     * @return \Codeages\Biz\Framework\Scheduler\Service\SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->getBiz()->service('Scheduler:SchedulerService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Item\Service\ItemService
     */
    protected function getItemService()
    {
        return $this->getBiz()->service('ItemBank:Item:ItemService');
    }
}
