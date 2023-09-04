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
        ];
    }

    public function onItemDelete(Event $event)
    {
        $item = $event->getSubject();
        $assessments = $this->getAssessmentService()->searchAssessments(['bank_id' => $item['bank_id'], 'displayable' => 1], [], 0, PHP_INT_MAX, ['id']);
        if (empty($assessments)) {
            return;
        }
        $sectionItems = $this->getAssessmentSectionItemService()->searchAssessmentSectionItems(['assessmentIds' => array_column($assessments, 'id'), 'item_id' => $item['id']], [], 0, PHP_INT_MAX, ['assessment_id']);
        if (empty($sectionItems)) {
            return;
        }
        $this->getAssessmentService()->createAssessmentSnapshotsIncludeSectionsAndItems(array_column($sectionItems, 'assessment_id'));
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
    protected function getAssessmentSectionItemService()
    {
        return $this->getBiz()->service('ItemBank:Assessment:AssessmentSectionItemService');
    }
}
