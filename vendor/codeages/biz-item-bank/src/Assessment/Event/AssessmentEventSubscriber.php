<?php

namespace Codeages\Biz\ItemBank\Assessment\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReportService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentSectionItemService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

class AssessmentEventSubscriber extends EventSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            'item.delete' => 'onItemDelete',
            'item.batchDelete' => 'onItemBatchDelete',
            'assessment.before_update' => 'onAssessmentBeforeUpdate',
        ];
    }

    public function onItemDelete(Event $event)
    {
        $item = $event->getSubject();
        $this->processOnItemsDelete($item['bank_id'], [$item]);
    }

    public function onItemBatchDelete(Event $event)
    {
        $deleteItems = $event->getSubject();
        $this->processOnItemsDelete(current($deleteItems)['bank_id'], $deleteItems);
    }

    private function processOnItemsDelete($bankId, $deleteItems)
    {
        $assessments = $this->getAssessmentService()->searchAssessments(['bank_id' => $bankId, 'displayable' => 1], [], 0, PHP_INT_MAX, ['id']);
        if (empty($assessments)) {
            return;
        }
        $toDeleteSectionItems = $this->getAssessmentSectionItemService()->searchAssessmentSectionItems(
            ['assessmentIds' => array_column($assessments, 'id'), 'item_ids' => array_column($deleteItems, 'id')],
            [],
            0,
            PHP_INT_MAX,
            ['id', 'assessment_id', 'section_id', 'score', 'question_count']
        );
        if (empty($toDeleteSectionItems)) {
            return;
        }
        $assessmentSnapshots = $this->getAssessmentService()->createAssessmentSnapshotsIncludeSectionsAndItems(array_column($toDeleteSectionItems, 'assessment_id'));
        $this->getAnswerRecordService()->replaceAssessmentsWithSnapshotAssessments($assessmentSnapshots);
        $this->getAnswerReportService()->replaceAssessmentsWithSnapshotAssessments($assessmentSnapshots);
        $this->getAssessmentService()->modifyAssessmentsAndSectionsWithToDeleteSectionItems($toDeleteSectionItems);
        $this->getAssessmentSectionItemService()->deleteAssessmentSectionItems($toDeleteSectionItems);
    }

    public function onAssessmentBeforeUpdate(Event $event)
    {
        $answerRecord = $this->getAnswerRecordService()->countByAssessmentId($event->getSubject());
        if (empty($answerRecord)) {
            return;
        }
        $assessmentSnapshots = $this->getAssessmentService()->createAssessmentSnapshotsIncludeSectionsAndItems([$event->getSubject()]);
        $this->getAnswerRecordService()->replaceAssessmentsWithSnapshotAssessments($assessmentSnapshots);
        $this->getAnswerReportService()->replaceAssessmentsWithSnapshotAssessments($assessmentSnapshots);
    }

    /**
     * @return AssessmentService
     */
    private function getAssessmentService()
    {
        return $this->getBiz()->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return AssessmentSectionItemService
     */
    private function getAssessmentSectionItemService()
    {
        return $this->getBiz()->service('ItemBank:Assessment:AssessmentSectionItemService');
    }

    /**
     * @return AnswerRecordService
     */
    private function getAnswerRecordService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return AnswerReportService
     */
    private function getAnswerReportService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerReportService');
    }
}
