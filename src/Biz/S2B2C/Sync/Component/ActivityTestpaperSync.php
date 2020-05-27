<?php

namespace Biz\S2B2C\Sync\Component;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\TestpaperActivityService;
use Codeages\Biz\ItemBank\Assessment\Dao\AssessmentDao;
use Codeages\Biz\ItemBank\Assessment\Dao\Impl\AssessmentSectionDaoImpl;
use Codeages\Biz\ItemBank\Assessment\Dao\Impl\AssessmentSectionItemDaoImpl;

class ActivityTestpaperSync extends TestpaperSync
{
    /*
     * - $source = $activity
     * - $config: newActivity, isCopy
     * */
    protected function syncEntity($source, $config = [])
    {
        if (!in_array($source['mediaType'], ['testpaper', 'homework'])) {
            return [];
        }

        return $this->doSyncTestpaper($source, $config['newCourseSetId'], $config['newCourseId'], $config['isCopy'], $config['questionSyncIds']);
    }

    public function doSyncTestpaper($activity, $newCourseSetId, $newCourseId, $isCopy, $questionSyncIds)
    {
        $testpaper = $activity['testpaper'];
        if (empty($testpaper)) {
            return null;
        }

        $s2b2cConfig = $this->getS2B2CConfig();
        list($assessment, $assessmentSectionItems) = $this->createTestpaper($testpaper);
        $this->getResourceSyncService()->createSync([
            'supplierId' => $s2b2cConfig['supplierId'],
            'resourceType' => 'assessment',
            'localResourceId' => $assessment['id'],
            'remoteResourceId' => $testpaper['id'],
            'syncTime' => time(),
        ]);

        return $assessment;
    }

    public function getActivityConfig($type)
    {
        return $this->biz["activity_type.{$type}"];
    }

    protected function updateEntityToLastedVersion($source, $config = [])
    {
        if (!in_array($source['mediaType'], ['testpaper', 'homework'])) {
            return [];
        }

        $testpaper = $source['testpaper'];
        if (empty($testpaper)) {
            return null;
        }
        $s2b2cConfig = $this->getS2B2CConfig();

        $resourceSync = $this->getResourceSyncService()->getSyncBySupplierIdAndRemoteResourceIdAndResourceType(
            $s2b2cConfig['supplierId'],
            $testpaper['id'],
            'assessment'
        );

        if (!empty($resourceSync)) {
            /**
             * 已经存在的情况下无法更新，数据结构完全不一致
             */
            $assessment = $this->getAssessmentDao()->get($resourceSync['localResourceId']);
        } else {
            list($assessment, $assessmentSectionItems) = $this->createTestpaper($testpaper);
            $this->getResourceSyncService()->createSync([
                'supplierId' => $s2b2cConfig['supplierId'],
                'resourceType' => 'assessment',
                'localResourceId' => $assessment['id'],
                'remoteResourceId' => $testpaper['id'],
                'syncTime' => time(),
            ]);
        }

        return $assessment;
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->biz->service('Activity:TestpaperActivityService');
    }

    /**
     * @return AssessmentDao
     */
    protected function getAssessmentDao()
    {
        return $this->biz->dao('ItemBank:Assessment:AssessmentDao');
    }

    /**
     * @return AssessmentSectionDaoImpl
     */
    protected function getAssessmentSectionDao()
    {
        return $this->biz->dao('ItemBank:Assessment:AssessmentSectionDao');
    }

    /**
     * @return AssessmentSectionItemDaoImpl
     */
    protected function getAssessmentSectionItemDao()
    {
        return $this->biz->dao('ItemBank:Assessment:AssessmentSectionItemDao');
    }

    private function createTestpaper($testpaper)
    {
        $assessmentSectionItems = [];
        $questionCount = 0;
        $itemCount = 0;
        $items = $testpaper['items'];
        $assessment = $this->getAssessmentDao()->create([
            'bank_id' => $testpaper['bankId'],
            'displayable' => 'testpaper' == $testpaper['type'] ? 1 : 0,
            'name' => $testpaper['name'],
            'description' => $testpaper['description'],
            'total_score' => $testpaper['score'],
            'status' => $testpaper['status'],
            'item_count' => $itemCount,
            'question_count' => $questionCount,
            'created_user_id' => $testpaper['createdUserId'],
            'updated_user_id' => $testpaper['updatedUserId'],
            'created_time' => $testpaper['createdTime'],
            'updated_time' => $testpaper['updatedTime'],
        ]);
        if (count($items) > 0) {
            if ('homework' == $testpaper['type']) {
                $sectionAndItems = $this->getSectionAndItems($items, $testpaper, $assessment);
                $questionCount += $sectionAndItems['section']['question_count'];
                $itemCount += $sectionAndItems['section']['item_count'];
                $sectionAndItems['section']['name'] = '作业题目';
                $assessmentSectionItems = array_merge($assessmentSectionItems, $sectionAndItems['items']);
            } else {
                $dict = [
                    'single_choice' => '单选题',
                    'choice' => '多选题',
                    'essay' => '问答题',
                    'uncertain_choice' => '不定向选择题',
                    'determine' => '判断题',
                    'fill' => '填空题',
                    'material' => '材料题',
                ];
                $sections = ArrayToolkit::group($items, 'questionType');
                $sectionSeq = 1;
                foreach ($sections as $questionType => $sectionItems) {
                    if ('material' == $questionType) {
                        //把子题加回去
                        foreach ($items as $key => $item) {
                            if ($item['parentId'] > 0) {
                                $sectionItems[] = $item;
                            }
                        }
                    } else {
                        //把子题去掉
                        foreach ($sectionItems as $key => $item) {
                            if ($item['parentId'] > 0) {
                                unset($sectionItems[$key]);
                            }
                        }
                    }
                    if (empty($sectionItems)) {
                        continue;
                    }
                    $sectionAndItems = $this->getSectionAndItems(array_values($sectionItems), $testpaper, $assessment, $sectionSeq);
                    $questionCount += $sectionAndItems['section']['question_count'];
                    $itemCount += $sectionAndItems['section']['item_count'];
                    $sectionAndItems['section']['name'] = empty($dict[$questionType]) ? '其他' : $dict[$questionType];
                    $assessmentSectionItems = array_merge($assessmentSectionItems, $sectionAndItems['items']);
                    ++$sectionSeq;
                }
            }
        }

        $this->getAssessmentDao()->update($assessment['id'], [
            'item_count' => $itemCount,
            'question_count' => $questionCount,
        ]);
        $this->getAssessmentSectionItemDao()->batchCreate($assessmentSectionItems);

        return [$assessment, $assessmentSectionItems];
    }

    /**
     * @param $items
     * @param $testpaper
     * @param $assessment
     * @param int $sectionSeq
     *
     * @return array[]
     *                 questionRule 规则无法同步
     */
    protected function getSectionAndItems($items, $testpaper, $assessment, $sectionSeq = 1)
    {
        $itemCount = 0;
        $questionCount = 0;
        $assessmentItems = [];
        $sectionItems = [];
        $questions = [];
        foreach ($items as $item) {
            if ('material' != $item['questionType']) {
                if (0 != $item['parentId']) {
                    $questions[] = $item;
                }
                ++$questionCount;
            }
            if (0 == $item['parentId']) {
                $sectionItems[] = $item;
                ++$itemCount;
            }
        }
        $s2b2cConfig = $this->getS2B2CConfig();
        $resourceSyncs = ArrayToolkit::index($this->getResourceSyncService()->findSyncBySupplierIdAndRemoteResourceIdsAndResourceType(
            $s2b2cConfig['supplierId'],
            ArrayToolkit::column($items, 'questionId'),
            'question'
        ), 'remoteResourceId');
        $itemResourceSyncs = ArrayToolkit::index($this->getResourceSyncService()->findSyncBySupplierIdAndRemoteResourceIdsAndResourceType(
            $s2b2cConfig['supplierId'],
            ArrayToolkit::column($items, 'questionId'),
            'item'
        ), 'remoteResourceId');
        $questions = ArrayToolkit::group($questions, 'parentId');
        foreach ($sectionItems as $key => $sectionItem) {
            $subQuestions = empty($questions[$sectionItem['questionId']]) ? [$sectionItem] : $questions[$sectionItem['questionId']];
            $questionScores = [];
            $scoreRule = [];
            foreach ($subQuestions as $subQuestion) {
                $questionScores[] = [
                    'question_id' => empty($resourceSyncs[$subQuestion['questionId']]) ? $subQuestion['questionId'] : $resourceSyncs[$subQuestion['questionId']]['localResourceId'],
                    'score' => $subQuestion['score'],
                ];
                $rule = [
                    ['name' => 'all_right', 'score' => $subQuestion['score']],
                    ['name' => 'no_answer', 'score' => 0],
                    ['name' => 'wrong', 'score' => 0],
                ];
                if ($subQuestion['missScore'] > 0) {
                    $rule[] = ['name' => 'part_right', 'score' => $subQuestion['missScore']];
                }
                $scoreRule[] = [
                    'question_id' => empty($resourceSyncs[$subQuestion['questionId']]) ? $subQuestion['questionId'] : $resourceSyncs[$subQuestion['questionId']]['localResourceId'],
                    'seq' => $subQuestion['seq'],
                    'rule' => $rule,
                ];
            }
            $assessmentItems[] = [
                'assessment_id' => $assessment['id'],
                'item_id' => empty($itemResourceSyncs[$sectionItem['questionId']]) ? $sectionItem['questionId'] : $itemResourceSyncs[$sectionItem['questionId']]['localResourceId'],
                'section_id' => 0,
                'seq' => $key + 1,
                'score' => array_sum(ArrayToolkit::column($questionScores, 'score')),
                'question_count' => count($subQuestions),
                'question_scores' => $questionScores,
                'score_rule' => $scoreRule,
            ];
        }
        $section = $this->getAssessmentSectionDao()->create([
            'assessment_id' => $assessment['id'],
            'name' => '',
            'seq' => $sectionSeq,
            'item_count' => $itemCount,
            'question_count' => $questionCount,
            'total_score' => array_sum(ArrayToolkit::column($assessmentItems, 'score')),
        ]);
        foreach ($assessmentItems as &$assessmentItem) {
            $assessmentItem['section_id'] = $section['id'];
        }

        return [
            'section' => $section,
            'items' => $assessmentItems,
        ];
    }
}
