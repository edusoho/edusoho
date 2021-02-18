<?php

namespace Codeages\Biz\ItemBank\Assessment\Service\Impl;

use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentSectionItemService;
use Codeages\Biz\ItemBank\BaseService;
use Codeages\Biz\ItemBank\Assessment\Dao\AssessmentSectionDao;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentSectionService;

class AssessmentSectionServiceImpl extends BaseService implements AssessmentSectionService
{
    public function getAssessmentSection($id)
    {
        return $this->getAssessmentSectionDao()->get($id);
    }

    public function findSectionsByAssessmentId($assessmentId)
    {
        return $this->getAssessmentSectionDao()->findByAssessmentId($assessmentId);
    }

    public function findSectionDetailByAssessmentId($assessmentId)
    {
        $sections = $this->getAssessmentSectionDao()->findByAssessmentId($assessmentId);
        $assessmentItems = $this->getSectionItemService()->findSectionItemDetailByAssessmentId($assessmentId);
        $assessmentItems = ArrayToolkit::group($assessmentItems, 'section_id');
        foreach ($sections as &$section) {
            $section['items'] = empty($assessmentItems[$section['id']]) ? [] : $assessmentItems[$section['id']];
        }

        return $sections;
    }

    public function createAssessmentSection($assessmentId, $section)
    {
        try {
            $this->beginTransaction();
            $assessmentSection = [
                'assessment_id' => $assessmentId,
                'name' => $section['name'],
                'seq' => $section['seq'],
                'description' => empty($section['description']) ? '' : $section['description'],
            ];

            $assessmentSection = $this->getAssessmentSectionDao()->create($assessmentSection);

            $items = $this->createAssessmentItems($assessmentSection, $section['items']);

            $assessmentSection = $this->updateAssessmentSection($assessmentSection['id'], [
                'item_count' => count($items),
                'total_score' => array_sum(ArrayToolkit::column($items, 'score')),
                'question_count' => array_sum(ArrayToolkit::column($items, 'question_count')),
            ]);

            $this->commit();

            $assessmentSection['items'] = $items;

            return $assessmentSection;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    protected function createAssessmentItems($section, $items)
    {
        $assessmentItems = [];
        foreach ($items as $item) {
            $assessmentItems[] = $this->getSectionItemService()->createAssessmentSectionItem($item, $section);
        }

        return $assessmentItems;
    }

    public function updateAssessmentSection($id, $fields)
    {
        return $this->getAssessmentSectionDao()->update($id, $fields);
    }

    public function deleteAssessmentSection($id)
    {
        return $this->getAssessmentSectionDao()->delete($id);
    }

    public function deleteAssessmentSectionsByAssessmentId($assessmentId)
    {
        return $this->getAssessmentSectionDao()->deleteByAssessmentId($assessmentId);
    }

    public function countAssessmentSections($conditions)
    {
        return $this->getAssessmentSectionDao()->count($conditions);
    }

    public function searchAssessmentSections($conditions, $orderBys, $start, $limit, $columns = array())
    {
        return $this->getAssessmentSectionDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    /**
     * @return AssessmentSectionDao
     */
    protected function getAssessmentSectionDao()
    {
        return $this->biz->dao('ItemBank:Assessment:AssessmentSectionDao');
    }

    /**
     * @return AssessmentSectionItemService
     */
    protected function getSectionItemService()
    {
        return $this->biz->service('ItemBank:Assessment:AssessmentSectionItemService');
    }
}
