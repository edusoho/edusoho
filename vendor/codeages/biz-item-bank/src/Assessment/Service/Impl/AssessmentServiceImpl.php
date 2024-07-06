<?php

namespace Codeages\Biz\ItemBank\Assessment\Service\Impl;

use Codeages\Biz\ItemBank\Answer\Dao\AnswerQuestionReportDao;
use Codeages\Biz\ItemBank\Answer\Dao\AnswerRecordDao;
use Codeages\Biz\ItemBank\Answer\Dao\AnswerReportDao;
use Codeages\Biz\ItemBank\Answer\Dao\AnswerSceneDao;
use Codeages\Biz\ItemBank\Answer\Dao\AnswerSceneQuestionReportDao;
use Codeages\Biz\ItemBank\Assessment\Constant\AssessmentStatus;
use Codeages\Biz\ItemBank\Assessment\Dao\AssessmentSnapshotDao;
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
        $defaultAssessment = [
            'type' => 'regular',
            'parentId' => '0',
            'status' => 'draft',
            'parent_id' => '0'
        ];

        $assessment = array_merge($defaultAssessment, $assessment);
        $assessment = $this->getValidator()->validate($assessment, [
            'bank_id' => ['required', 'integer', ['min', 1]],
            'name' => ['required', ['lengthBetween', 1, 255]],
            'description' => [],
            'created_user_id' => ['integer', ['min', 0]],
            'item_count' => ['integer', ['min', 0]],
            'question_count' => ['integer', ['min', 0]],
            'displayable' => ['required', ['in', [0, 1]]],
            'type' => ['required', ['in', ['regular', 'random', 'ai_personality']]],
            'parent_id' => ['required', ['min', 0]],
            'status' => ['required', ['in', ['generating', 'draft']]],
        ]);

        $itemBank = $this->getItemBankService()->getItemBank($assessment['bank_id']);
        if (empty($itemBank)) {
            throw new ItemBankException('Item bank is not found.', ErrorCode::ITEM_BANK_NOT_FOUND);
        }

        isset($assessment['description']) && $assessment['description'] = $this->biz['item_bank_html_helper']->purify($assessment['description']);
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

            $this->processDeleteAssessment($assessment);

            $this->dispatch('assessment.delete', $assessment);

            $this->commit();

            return true;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function deleteAssessmentByParentId($parentId)
    {
        $assessment = $this->getAssessment($parentId);
        if (empty($assessment) || $assessment['parent_id'] != 0)  {
            throw AssessmentException::ASSESSMENT_NOTEXIST();
        }
        $assessmentIds = $this->getAssessmentDao()->search(['parent_id' => $parentId], [], 0, PHP_INT_MAX, ['id']);
        $assessmentIds = array_column($assessmentIds, 'id');
        if (empty($assessmentIds)) {
            throw AssessmentException::ASSESSMENT_NOTEXIST();
        }

        try {
            $this->beginTransaction();
            $this->getAssessmentDao()->batchDelete(['ids'=> $assessmentIds]);
            $this->processBatchDeleteAssessment($assessmentIds);
            $this->dispatch('assessment.batch.delete', $assessmentIds);
            $this->commit();

            return true;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    protected function processBatchDeleteAssessment($assessmentIds){
        $this->getSectionService()->deleteAssessmentSectionsByAssessmentIds($assessmentIds);
        $this->getSectionItemService()->deleteAssessmentSectionItemsByAssessmentIds($assessmentIds);
        $daoArr = ['AnswerReportDao', 'AnswerRecordDao', 'AnswerQuestionReportDao'];
        foreach ($daoArr as $dao){
            $this->biz->dao('ItemBank:Answer:'.$dao)->batchDelete(['assessment_ids' => $assessmentIds]);
        }
    }

    protected function processDeleteAssessment($assessment){
        $this->getSectionService()->deleteAssessmentSectionsByAssessmentId($assessment['id']);
        $this->getSectionItemService()->deleteAssessmentSectionItemsByAssessmentId($assessment['id']);
        if (1 == $assessment['displayable']) {
            $this->getItemBankService()->updateAssessmentNum($assessment['bank_id'], -1);
        }
        $daoArr = ['AnswerReportDao', 'AnswerRecordDao', 'AnswerQuestionReportDao'];
        foreach ($daoArr as $dao){
            $this->biz->dao('ItemBank:Answer:'.$dao)->deleteByAssessmentId($assessment['id']);
        }
    }

    public function updateAssessment($assessmentId, $assessment)
    {
        if (empty($this->getAssessment($assessmentId))) {
            throw new AssessmentException('Assessment not found', ErrorCode::ASSESSMENT_NOTFOUND);
        }

        try {
            $this->beginTransaction();
            $this->dispatchEvent('assessment.before_update', $assessmentId);

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
            'status' => [['in', [self::GENERATING, self::DRAFT, self::OPEN, self::CLOSED, self::FAILURE]]],
            'item_count' => ['integer', ['min', 0]],
            'question_count' => ['integer', ['min', 0]],
            'total_score' => [],
            'description' => [],
        ]);
        $assessment['updated_user_id'] = empty($assessment['updated_user_id']) ? empty($this->biz['user']['id']) ? 0 : $this->biz['user']['id'] : $assessment['updated_user_id'];
        isset($assessment['description']) && $assessment['description'] = $this->biz['item_bank_html_helper']->purify($assessment['description']);

        return $this->getAssessmentDao()->update($assessmentId, $assessment);
    }

    public function updateBasicAssessmentByParentId($parentId, $assessment)
    {
        $ids = $this->searchAssessments(['parent_id' => $parentId], [] , 0, PHP_INT_MAX, ['id']);
        if (empty($ids)) {
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
        isset($assessment['description']) && $assessment['description'] = $this->biz['item_bank_html_helper']->purify($assessment['description']);

        return $this->getAssessmentDao()->update(['ids' => array_column($ids, 'id')], $assessment);
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

    public function countAssessmentItemTypesNum($assessmentId)
    {
        $assessmentItems = $this->getSectionItemService()->findSectionItemsByAssessmentId($assessmentId);
        $items = $this->getItemService()->findItemsByIds(array_column($assessmentItems, 'item_id'));

        return $this->getItemService()->countItemTypesNum($items);
    }

    public function createAssessmentSnapshotsIncludeSectionsAndItems(array $assessmentIds)
    {
        if (empty($assessmentIds)) {
            return;
        }
        $assessments = $this->findAssessmentsByIds($assessmentIds);
        $assessmentSnapshots = $this->createAssessmentSnapshots($assessments);
        $this->createSnapshotAssessmentSectionsAndItems($assessmentSnapshots);

        return $this->getAssessmentSnapshotDao()->findBySnapshotAssessmentIds(array_column($assessmentSnapshots, 'snapshot_assessment_id'));
    }

    public function modifyAssessmentsAndSectionsWithToDeleteSectionItems(array $toDeleteSectionItems)
    {
        if (empty($toDeleteSectionItems)) {
            return;
        }
        $toDeleteSectionIds = $this->extractToDeleteSectionIds($toDeleteSectionItems);
        $this->getSectionService()->deleteAssessmentSections($toDeleteSectionIds);

        $eachAssessmentToUpdateSections = $this->extractEachAssessmentToUpdateSections($toDeleteSectionItems);

        $toUpdateSections = $this->extractToUpdateSections($eachAssessmentToUpdateSections);
        $this->getSectionService()->updateAssessmentSections($toUpdateSections);

        $toUpdateAssessments = $this->extractToUpdateAssessments($eachAssessmentToUpdateSections);
        if ($toUpdateAssessments) {
            $this->getAssessmentDao()->batchUpdate(array_keys($toUpdateAssessments), $toUpdateAssessments);
        }
    }

    public function getAssessmentSnapshotBySnapshotAssessmentId($snapshotAssessmentId)
    {
        return $this->getAssessmentSnapshotDao()->getBySnapshotAssessmentId($snapshotAssessmentId);
    }

    public function isEmptyAssessment($assessmentId)
    {
        $assessment = $this->getAssessment($assessmentId);

        return empty($assessment['item_count']);
    }

    private function createAssessmentSnapshots($assessments)
    {
        $assessmentSnapshots = [];
        foreach ($assessments as $assessment) {
            if (AssessmentStatus::DRAFT == $assessment['status']) {
                continue;
            }
            $assessmentSnapshot = ['origin_assessment_id' => $assessment['id']];
            unset($assessment['id']);
            $assessment['displayable'] = 0;
            $snapshotAssessment = $this->getAssessmentDao()->create($assessment);
            $assessmentSnapshot['snapshot_assessment_id'] = $snapshotAssessment['id'];
            $assessmentSnapshots[] = $assessmentSnapshot;
        }
        if ($assessmentSnapshots) {
            $this->getAssessmentSnapshotDao()->batchCreate($assessmentSnapshots);
        }

        return $assessmentSnapshots;
    }

    private function createSnapshotAssessmentSectionsAndItems($assessmentSnapshots)
    {
        if (empty($assessmentSnapshots)) {
            return;
        }
        $originAssessmentIds = array_column($assessmentSnapshots, 'origin_assessment_id');
        $originAssessmentSections = $this->getSectionService()->findSectionsByAssessmentIds($originAssessmentIds);
        $assessmentSnapshots = array_column($assessmentSnapshots, null, 'origin_assessment_id');
        $assessmentSectionSnapshots = $this->createSnapshotAssessmentSections($originAssessmentSections, $assessmentSnapshots);
        $this->createSnapshotAssessmentSectionItems($originAssessmentIds, $assessmentSnapshots, $assessmentSectionSnapshots);
    }

    private function createSnapshotAssessmentSections($originAssessmentSections, $assessmentSnapshots)
    {
        $snapshotAssessmentSections = [];
        foreach ($originAssessmentSections as $originAssessmentSection) {
            $originAssessmentSection['assessment_id'] = $assessmentSnapshots[$originAssessmentSection['assessment_id']]['snapshot_assessment_id'];
            unset($originAssessmentSection['id']);
            $snapshotAssessmentSections[] = $originAssessmentSection;
        }
        $this->getSectionService()->createAssessmentSections($snapshotAssessmentSections);
        $snapshotAssessmentSections = $this->getSectionService()->findSectionsByAssessmentIds(array_column($assessmentSnapshots, 'snapshot_assessment_id'));

        return $this->buildAssessmentSectionSnapshots($assessmentSnapshots, $originAssessmentSections, $snapshotAssessmentSections);
    }

    private function createSnapshotAssessmentSectionItems($originAssessmentIds, $assessmentSnapshots, $assessmentSectionSnapshots)
    {
        $originAssessmentSectionItems = $this->getSectionItemService()->findSectionItemsByAssessmentIds($originAssessmentIds);
        $snapshotAssessmentSectionItems = [];
        foreach ($originAssessmentSectionItems as $originAssessmentSectionItem) {
            $originAssessmentSectionItem['assessment_id'] = $assessmentSnapshots[$originAssessmentSectionItem['assessment_id']]['snapshot_assessment_id'];
            $originAssessmentSectionItem['section_id'] = $assessmentSectionSnapshots[$originAssessmentSectionItem['section_id']];
            unset($originAssessmentSectionItem['id']);
            $snapshotAssessmentSectionItems[] = $originAssessmentSectionItem;
        }
        $this->getSectionItemService()->createAssessmentSectionItems($snapshotAssessmentSectionItems);
    }

    private function buildAssessmentSectionSnapshots($assessmentSnapshots, $originAssessmentSections, $snapshotAssessmentSections)
    {
        $snapshotAssessmentSections = ArrayToolkit::groupIndex($snapshotAssessmentSections, 'assessment_id', 'seq');
        $assessmentSectionSnapshots = [];
        $updateAssessmentSnapshots = [];
        foreach ($originAssessmentSections as $originAssessmentSection) {
            $snapshotAssessmentId = $assessmentSnapshots[$originAssessmentSection['assessment_id']]['snapshot_assessment_id'];
            $assessmentSectionSnapshots[$originAssessmentSection['id']] = $snapshotAssessmentSections[$snapshotAssessmentId][$originAssessmentSection['seq']]['id'];

            $updateAssessmentSnapshots[$snapshotAssessmentId]['sections_snapshot'] = $updateAssessmentSnapshots[$snapshotAssessmentId]['sections_snapshot'] ?? [];
            $updateAssessmentSnapshots[$snapshotAssessmentId]['sections_snapshot'][$originAssessmentSection['id']] = $assessmentSectionSnapshots[$originAssessmentSection['id']];
        }
        if ($updateAssessmentSnapshots) {
            $this->getAssessmentSnapshotDao()->batchUpdate(array_keys($updateAssessmentSnapshots), $updateAssessmentSnapshots, 'snapshot_assessment_id');
        }

        return $assessmentSectionSnapshots;
    }

    private function extractToDeleteSectionIds($toDeleteSectionItems)
    {
        $sectionIds = ArrayToolkit::uniqueColumn($toDeleteSectionItems, 'section_id');
        $sections = $this->getSectionService()->searchAssessmentSections(
            ['ids' => $sectionIds],
            [],
            0,
            count($sectionIds),
            ['id', 'item_count']
        );
        $toDeleteSectionItems = ArrayToolkit::group($toDeleteSectionItems, 'section_id');
        $toDeleteSectionIds = [];
        foreach ($sections as $section) {
            if (count($toDeleteSectionItems[$section['id']]) == $section['item_count']) {
                $toDeleteSectionIds[] = $section['id'];
            }
        }

        return $toDeleteSectionIds;
    }

    private function extractEachAssessmentToUpdateSections($toDeleteSectionItems)
    {
        $assessmentIds = ArrayToolkit::uniqueColumn($toDeleteSectionItems, 'assessment_id');
        $sections = $this->getSectionService()->searchAssessmentSections(['assessmentIds' => $assessmentIds], ['assessment_id' => 'ASC', 'seq' => 'ASC'], 0, PHP_INT_MAX);
        $toDeleteSectionItems = ArrayToolkit::group($toDeleteSectionItems, 'section_id');

        $eachAssessmentToUpdateSections = [];
        foreach ($sections as $section) {
            $eachAssessmentToUpdateSections[$section['assessment_id']] = $eachAssessmentToUpdateSections[$section['assessment_id']] ?? [];
            if (!empty($toDeleteSectionItems[$section['id']])) {
                $section['item_count'] -= count($toDeleteSectionItems[$section['id']]);
                $section['total_score'] -= array_sum(array_column($toDeleteSectionItems[$section['id']], 'score'));
                $section['question_count'] -= array_sum(array_column($toDeleteSectionItems[$section['id']], 'question_count'));
            }
            $eachAssessmentToUpdateSections[$section['assessment_id']][] = $section;
        }
        foreach ($assessmentIds as $assessmentId) {
            $eachAssessmentToUpdateSections[$assessmentId] = $eachAssessmentToUpdateSections[$assessmentId] ?? [];
        }

        return $eachAssessmentToUpdateSections;
    }

    private function extractToUpdateSections($eachAssessmentToUpdateSections)
    {
        $toUpdateSections = [];
        foreach ($eachAssessmentToUpdateSections as $singleAssessmentToUpdateSections) {
            foreach ($singleAssessmentToUpdateSections as $index => $toUpdateSection) {
                $toUpdateSections[$toUpdateSection['id']] = [
                    'seq' => $index + 1,
                    'total_score' => $toUpdateSection['total_score'],
                    'item_count' => $toUpdateSection['item_count'],
                    'question_count' => $toUpdateSection['question_count'],
                ];
            }
        }

        return $toUpdateSections;
    }

    private function extractToUpdateAssessments($eachAssessmentToUpdateSections)
    {
        $toUpdateAssessments = [];
        foreach ($eachAssessmentToUpdateSections as $assessmentId => $singleAssessmentToUpdateSections) {
            $toUpdateAssessments[$assessmentId] = [
                'total_score' => array_sum(array_column($singleAssessmentToUpdateSections, 'total_score')),
                'item_count' => array_sum(array_column($singleAssessmentToUpdateSections, 'item_count')),
                'question_count' => array_sum(array_column($singleAssessmentToUpdateSections, 'question_count')),
            ];
        }

        return $toUpdateAssessments;
    }

    protected function findExportItems($sections, $sectionItems)
    {
        $exportItems = [];
        $sectionItems = ArrayToolkit::group($sectionItems, 'section_id');
        foreach ($sections as $section) {
            if (empty($sectionItems[$section['id']])) {
                continue;
            }

            $itemIds = ArrayToolkit::column($sectionItems[$section['id']], 'item_id');

            $items = $this->getItemService()->findItemsByIds($itemIds);
            $items = ArrayToolkit::index($items,'id');
            foreach ($itemIds as $id) {
                if (!isset($items[$id])) {
                    continue;
                }
                $exportItems[] = $items[$id];
            }
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

    /**
     * @return AssessmentSnapshotDao
     */
    protected function getAssessmentSnapshotDao()
    {
        return $this->biz->dao('ItemBank:Assessment:AssessmentSnapshotDao');
    }
}
