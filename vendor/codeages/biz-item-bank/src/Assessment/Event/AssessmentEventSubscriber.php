<?php

namespace Codeages\Biz\ItemBank\Assessment\Event;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentSectionItemService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

class AssessmentEventSubscriber extends EventSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            'item.delete' => 'onItemDelete',
            'item.batchDelete' => 'onItemBatchDelete',
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
        $toDeletesectionItems = $this->getAssessmentSectionItemService()->searchAssessmentSectionItems(
            ['assessmentIds' => array_column($assessments, 'id'), 'item_ids' => array_column($deleteItems, 'id')],
            [],
            0,
            PHP_INT_MAX,
            ['assessment_id', 'section_id', 'score', 'question_count']
        );
        if (empty($toDeletesectionItems)) {
            return;
        }
        $this->getAssessmentService()->createAssessmentSnapshotsIncludeSectionsAndItems(array_column($toDeletesectionItems, 'assessment_id'));
        $this->getAssessmentService()->modifyAssessmentsAndSectionsWithToDeleteSectionItems($toDeletesectionItems);
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
}
