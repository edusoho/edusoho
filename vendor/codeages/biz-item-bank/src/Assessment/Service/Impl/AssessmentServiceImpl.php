<?php

namespace Codeages\Biz\ItemBank\Assessment\Service\Impl;

use Codeages\Biz\ItemBank\Assessment\Exception\AssessmentException;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentSectionItemService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentSectionService;
use Codeages\Biz\ItemBank\BaseService;
use Codeages\Biz\ItemBank\Assessment\Dao\AssessmentDao;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\ItemBank\Item\Service\ItemService;
use Codeages\Biz\ItemBank\ErrorCode;
use Codeages\Biz\ItemBank\Item\Wrapper\ExportItemsWrapper;
use Codeages\Biz\ItemBank\ItemBank\Exception\ItemBankException;
use Codeages\Biz\ItemBank\ItemBank\Service\ItemBankService;
use ExamParser\Writer\WriteDocx;

class AssessmentServiceImpl extends BaseService implements AssessmentService
{
    public function getAssessment($id)
    {
        return $this->getAssessmentDao()->get($id);
    }

    public function findAssessmentsByIds($assessmentIds)
    {
        return ArrayToolkit::index($this->getAssessmentDao()->findByIds($assessmentIds), 'id');
    }

    public function showAssessment($assessmentId)
    {
        $assessment = $this->getAssessment($assessmentId);
        if (empty($assessment)) {
            return [];
        }

        $assessment['sections'] = $this->getSectionService()->findSectionDetailByAssessmentId($assessmentId);

        return $assessment;
    }

    public function createAssessment($assessment)
    {
        try {
            $this->beginTransaction();

            $basicAssessment = $this->createBasicAssessment($assessment);

            $assessment = $this->createAssessmentSectionsAndItems($basicAssessment['id'], $assessment['sections']);

            $this->commit();

            return $assessment;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function createBasicAssessment($assessment)
    {
        $assessment = $this->getValidator()->validate($assessment, [
            'bank_id' => ['required', 'integer', ['min', 1]],
            'name' => ['required', ['lengthBetween', 1, 255]],
            'description' => [],
            'created_user_id' => ['integer', ['min', 0]],
            'item_count' => ['integer', ['min', 0]],
            'question_count' => ['integer', ['min', 0]],
            'displayable' => ['required', ['in', [0, 1]]],
        ]);

        $itemBank = $this->getItemBankService()->getItemBank($assessment['bank_id']);
        if (empty($itemBank)) {
            throw new ItemBankException('Item bank is not found.', ErrorCode::ITEM_BANK_NOT_FOUND);
        }

        $assessment['created_user_id'] = empty($assessment['created_user_id']) ? empty($this->biz['user']['id']) ? 0 : $this->biz['user']['id'] : $assessment['created_user_id'];
        $assessment['updated_user_id'] = $assessment['created_user_id'];

        $assessment = $this->getAssessmentDao()->create($assessment);

        if (1 == $assessment['displayable']) {
            $this->getItemBankService()->updateAssessmentNum($assessment['bank_id'], 1);
        }

        $this->dispatch('assessment.create', $assessment);

        return $assessment;
    }

    public function importAssessment($assessment)
    {
        try {
            $this->beginTransaction();

            $basicAssessment = $this->createBasicAssessment($assessment);

            foreach ($assessment['sections'] as &$section) {
                $savedItems = $this->getItemService()->importItems($section['items'], $basicAssessment['bank_id']);
                $items = $this->getItemService()->findItemsByIds(ArrayToolkit::column($savedItems, 'id'), true);
                foreach ($savedItems as &$savedItem) {
                    $savedItem = array_merge($savedItem, $items[$savedItem['id']]);
                }
                $section['items'] = $savedItems;
            }

            $assessment = $this->createAssessmentSectionsAndItems($basicAssessment['id'], $assessment['sections']);

            $this->commit();

            return $assessment;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function deleteAssessment($assessmentId)
    {
        $assessment = $this->getAssessment($assessmentId);
        if (empty($assessment)) {
            throw new AssessmentException('assessment not found', ErrorCode::ASSESSMENT_NOTFOUND);
        }

        try {
            $this->beginTransaction();

            $this->getAssessmentDao()->delete($assessmentId);

            $this->getSectionService()->deleteAssessmentSectionsByAssessmentId($assessmentId);

            $this->getSectionItemService()->deleteAssessmentSectionItemsByAssessmentId($assessmentId);

            if (1 == $assessment['displayable']) {
                $this->getItemBankService()->updateAssessmentNum($assessment['bank_id'], -1);
            }

            $this->dispatch('assessment.delete', $assessment);

            $this->commit();

            return true;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function updateAssessment($assessmentId, $assessment)
    {
        if (empty($this->getAssessment($assessmentId))) {
            throw new AssessmentException('Assessment not found', ErrorCode::ASSESSMENT_NOTFOUND);
        }

        try {
            $this->beginTransaction();

            if (!empty($assessment['sections'])) {
                $this->getSectionService()->deleteAssessmentSectionsByAssessmentId($assessmentId);

                $this->getSectionItemService()->deleteAssessmentSectionItemsByAssessmentId($assessmentId);

                $this->createAssessmentSectionsAndItems($assessmentId, $assessment['sections']);
            }

            $assessment = $this->updateBasicAssessment($assessmentId, $assessment);

            $this->dispatch('assessment.update', $assessment);

            $this->commit();

            return $assessment;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function updateBasicAssessment($assessmentId, $assessment)
    {
        if (empty($this->getAssessment($assessmentId))) {
            throw new AssessmentException('Assessment not found', ErrorCode::ASSESSMENT_NOTFOUND);
        }

        $assessment = $this->getValidator()->validate($assessment, [
            'bank_id' => ['integer', ['min', 1]],
            'name' => [['lengthBetween', 1, 255]],
            'updated_user_id' => ['integer', ['min', 0]],
            'status' => [['in', [self::DRAFT, self::OPEN, self::CLOSED]]],
            'item_count' => ['integer', ['min', 0]],
            'question_count' => ['integer', ['min', 0]],
            'total_score' => [],
            'description' => [],
        ]);
        $assessment['updated_user_id'] = empty($assessment['updated_user_id']) ? empty($this->biz['user']['id']) ? 0 : $this->biz['user']['id'] : $assessment['updated_user_id'];

        return $this->getAssessmentDao()->update($assessmentId, $assessment);
    }

    protected function createAssessmentSectionsAndItems($assessmentId, $sections)
    {
        $assessmentSections = [];
        $sections = $this->setSeq($sections);
        foreach ($sections as $section) {
            if (empty($section['items'])) {
                continue;
            }
            $assessmentSections[] = $this->getSectionService()->createAssessmentSection($assessmentId, $section);
        }

        $assessment = $this->updateBasicAssessment($assessmentId, [
            'total_score' => array_sum(ArrayToolkit::column($assessmentSections, 'total_score')),
            'item_count' => array_sum(ArrayToolkit::column($assessmentSections, 'item_count')),
            'question_count' => array_sum(ArrayToolkit::column($assessmentSections, 'question_count')),
        ]);
        $assessment['sections'] = $assessmentSections;

        return $assessment;
    }

    protected function setSeq($sections)
    {
        $sectionSeq = 1;
        $itemSeq = 1;
        $questionSeq = 1;
        foreach ($sections as &$section) {
            $section['seq'] = $sectionSeq++;
            foreach ($section['items'] as &$item) {
                $item['seq'] = $itemSeq++;
                foreach ($item['questions'] as &$question) {
                    $question['seq'] = $questionSeq++;
                }
            }
        }

        return $sections;
    }

    public function drawItems($range, $sections)
    {
        $range = $this->getValidator()->validate($range, [
            'bank_id' => ['required'],
            'category_ids' => ['array'],
            'difficulty' => [['in', ['difficulty', 'normal', 'simple']]],
            'item_types' => ['array'],
            'item_count' => ['integer', ['min', 0]],
        ]);

        $helper = $this->getItemDrawHelper();

        $sections = $helper->drawItems($range, $sections);

        return $this->setSeq($sections);
    }

    public function countAssessments($conditions)
    {
        $conditions = $this->filterConditions($conditions);

        return $this->getAssessmentDao()->count($conditions);
    }

    public function searchAssessments($conditions, $orderBys, $start, $limit, $columns = array())
    {
        $conditions = $this->filterConditions($conditions);

        return $this->getAssessmentDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    protected function filterConditions($conditions)
    {
        if (!empty($conditions['nameLike'])) {
            $conditions['nameLike'] = trim($conditions['nameLike']);
        }

        return $conditions;
    }

    public function openAssessment($id)
    {
        $assessment = $this->getAssessment($id);
        if (empty($assessment)) {
            throw new AssessmentException('Assessment not found', ErrorCode::ASSESSMENT_NOTFOUND);
        }

        if (self::OPEN == $assessment['status']) {
            throw new AssessmentException('Assessment is opened already', ErrorCode::ASSESSMENT_STATUS_ERROR);
        }

        return $this->getAssessmentDao()->update($id, ['status' => self::OPEN]);
    }

    public function closeAssessment($id)
    {
        $assessment = $this->getAssessment($id);
        if (empty($assessment)) {
            throw new AssessmentException('Assessment not found', ErrorCode::ASSESSMENT_NOTFOUND);
        }

        if (self::OPEN != $assessment['status']) {
            throw new AssessmentException('Assessment not open', ErrorCode::ASSESSMENT_STATUS_ERROR);
        }

        return $this->getAssessmentDao()->update($id, ['status' => self::CLOSED]);
    }

    public function review($assessmentId, $sectionResponses)
    {
        $assessment = $this->getAssessment($assessmentId);
        if (empty($assessment)) {
            throw new AssessmentException('Assessment not found', ErrorCode::ASSESSMENT_NOTFOUND);
        }

        return $this->getAssessmentReviewHelper()->review($assessment, $sectionResponses);
    }

    public function exportAssessment($assessmentId, $path, $imgRootDir)
    {
        $assessment = $this->getAssessment($assessmentId);
        if (empty($assessment)) {
            throw new AssessmentException('Assessment not found', ErrorCode::ASSESSMENT_NOTFOUND);
        }

        $sections = $this->getSectionService()->findSectionsByAssessmentId($assessmentId);
        $sectionItems = $this->getSectionItemService()->findSectionItemsByAssessmentId($assessmentId);
        if (empty($sections) || empty($sectionItems)) {
            return false;
        }

        $exportItems = $this->findExportItems($sections, $sectionItems);
        $exportItems = $this->getExportItemsWrapper($imgRootDir)->wrap($exportItems);

        $writer = new WriteDocx($path);
        $writer->write($exportItems);

        return true;
    }

    public function findAssessmentQuestions($assessmentId)
    {
        $assessmentItems = $this->getSectionItemService()->findSectionItemsByAssessmentId($assessmentId);
        if (empty($assessmentItems)) {
            return [];
        }
        $questions = [];
        foreach ($assessmentItems as $item) {
            foreach ($item['question_scores'] as $index => $questionScore) {
                $questions[] = [
                    'question_id' => $questionScore['question_id'],
                    'item_id' => $item['item_id'],
                    'section_id' => $item['section_id'],
                    'score' => $questionScore['score'],
                    'seq' => $item['score_rule'][$index]['seq'],
                ];
            }
        }
        return ArrayToolkit::index($questions, 'question_id');
    }

    protected function findExportItems($sections, $sectionItems)
    {
        $exportItems = [];
        $sectionItems = ArrayToolkit::group($sectionItems, 'section_id');
        foreach ($sections as $section) {
            if (empty($sectionItems[$section['id']])) {
                continue;
            }

            $items = $this->getItemService()->findItemsByIds(ArrayToolkit::column($sectionItems[$section['id']], 'item_id'));
            $exportItems = array_merge($exportItems, $items);
        }

        return $exportItems;
    }

    /**
     * @param $imgRootDir
     *
     * @return ExportItemsWrapper
     */
    protected function getExportItemsWrapper($imgRootDir)
    {
        $exportItemsWrapper = $this->biz['export_items_wrapper'];
        $exportItemsWrapper->setImgRootDir($imgRootDir);

        return $exportItemsWrapper;
    }

    protected function getItemDrawHelper()
    {
        return $this->biz['item_draw_helper'];
    }

    protected function getAssessmentReviewHelper()
    {
        return $this->biz['assessment_review_helper'];
    }

    /**
     * @return AssessmentDao
     */
    protected function getAssessmentDao()
    {
        return $this->biz->dao('ItemBank:Assessment:AssessmentDao');
    }

    /**
     * @return AssessmentSectionService
     */
    protected function getSectionService()
    {
        return $this->biz->service('ItemBank:Assessment:AssessmentSectionService');
    }

    /**
     * @return AssessmentSectionItemService
     */
    protected function getSectionItemService()
    {
        return $this->biz->service('ItemBank:Assessment:AssessmentSectionItemService');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->biz->service('ItemBank:Item:ItemService');
    }

    /**
     * @return ItemBankService
     */
    protected function getItemBankService()
    {
        return $this->biz->service('ItemBank:ItemBank:ItemBankService');
    }
}
