<?php

namespace Biz\Testpaper\Builder;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

class RandomTestpaperBuilder implements TestpaperBuilderInterface
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function build($fields)
    {
        $sections = $this->getSections($fields);
        $sections = $this->setSectionQuestionScore($sections, $fields['type']);
        $assessment = [
            'bank_id' => $fields['itemBankId'],
            'name' => $fields['name'],
            'displayable' => $fields['displayable'] ?? 1,
            'description' => $fields['description'],
            'sections' => $sections,
            'type' => $fields['type'] ?? 'regular',
            'parent_id' => $fields['parentId'] ?? '0',
            'status' => $fields['status'] ?? 'draft',
        ];

        return $this->getAssessmentService()->createAssessment($assessment);
    }

    public function canBuild($options)
    {
        try {
            $this->getSections($options);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function getSections($fields)
    {
        $generateType = $fields['type'] ?? 'default';
        $methodName = 'getSectionsBy'.ucfirst($generateType);

        return $this->$methodName($fields);
    }

    /**
     * @param $fields
     *
     * @return mixed
     *               原来智能组卷逻辑
     */
    private function getSectionsByDefault($fields)
    {
        list($range, $sections) = $this->getRangeAndSections($fields);

        return $this->getAssessmentService()->drawItems($range, $sections);
    }

    /**
     * @param $fields
     *
     * @return array
     *               随机卷题目组卷逻辑
     */
    private function getSectionsByRandom($fields)
    {
        $itemsMerged = [];
        foreach ($fields['questionCategoryCounts'] as $questionCategoryCount) {
            list($range, $sections) = $this->getRangeAndSectionsByQuestionTypeCategory($fields, $questionCategoryCount);
            $drawItems = $this->getAssessmentService()->drawItems($range, $sections);
            $itemsMerged = $this->mergeItem($itemsMerged, $drawItems);
        }

        return $itemsMerged;
    }

    private function getSectionsByAiPersonality(array $fields)
    {
        list($range, $sections) = $this->getRangeAndSections($fields);
        if (!empty($fields['itemIds'])) {
            $this->getRangeAndSectionsByWrongItems($sections, $fields['itemIds']);
        }

        return $this->getAssessmentService()->drawItems($range, $sections);
    }

    private function mergeItem(&$itemsMerged, $drawItems)
    {
        if (empty($itemsMerged)) {
            return $drawItems;
        }
        if (empty($drawItems)) {
            return $itemsMerged;
        }
        $drawItemsMap = array_map(function ($item) {
            $itemType = $item['conditions']['item_types'][0];

            return [$itemType => $item];
        }, $drawItems);
        foreach ($itemsMerged as $key => &$item) {
            $itemType = $item['conditions']['item_types'][0];
            $drawItem = $drawItemsMap[$key][$itemType];
            if (empty($drawItem['items'])) {
                continue;
            }

            $item['item_count'] += $drawItem['item_count'];
            $item['items'] = array_merge($item['items'], $drawItem['items']);
            $item['question_count'] += $drawItem['question_count'];
        }

        return $itemsMerged;
    }

    public function showTestItems($testId, $resultId = 0, $options = [])
    {
    }

    public function updateSubmitedResult($resultId, $usedTime, $options = [])
    {
    }

    protected function getRangeAndSections($fields)
    {
        $range = [
            'bank_id' => $fields['itemBankId'],
            'category_ids' => [],
        ];

        //重新构建category_ids分类参数
        if ((int) $fields['ranges']['categoryId']) {
            $categoryIds = $this->getItemCategoryService()->findCategoryChildrenIds($fields['ranges']['categoryId']);
            if ($categoryIds) {
                $categoryIds[] = $fields['ranges']['categoryId'];
                $range['category_ids'] = $categoryIds;
            } else {
                $range['category_ids'] = [$fields['ranges']['categoryId']];
            }
        }

        $sections = [];
        foreach ($fields['sections'] as $type => $section) {
            $section = [
                'conditions' => [
                    'item_types' => [$type],
                ],
                'item_count' => $section['count'],
                'name' => $section['name'],
                'score' => empty($fields['scores'][$type]) ? 0 : $fields['scores'][$type],
            ];

            if (isset($fields['choiceScore'][$type])) {
                $section['choiceScore'] = $fields['choiceScore'][$type];
            }

            if (!empty($fields['scoreType'][$type])) {
                $section['scoreType'] = $fields['scoreType'][$type];
            }

            if ('difficulty' == $fields['mode']) {
                $section['conditions']['distribution'] = $fields['percentages'];
            }

            $sections[] = $section;
        }

        return [$range, $sections];
    }

    protected function getRangeAndSectionsByWrongItems(&$sections, $wrongItemIds)
    {
        foreach ($sections as &$section) {
            if (isset($wrongItemIds[$section['conditions']['item_types'][0]])) {
                $itemIdsForType = array_keys($wrongItemIds[$section['conditions']['item_types'][0]]);
                $section['conditions']['itemIds'] = $itemIdsForType;
            } else {
                $section['conditions']['itemIds'] = [];
            }
        }
    }

    protected function getRangeAndSectionsByQuestionTypeCategory($fields, $questionCategoryCount)
    {
        $range = [
            'bank_id' => $fields['itemBankId'],
            'category_ids' => [],
        ];

        $sections = [];
        if ((int) $questionCategoryCount['categoryId']) {
            $range['category_ids'][] = $questionCategoryCount['categoryId'];
        }
        foreach ($questionCategoryCount['counts'] as $type => $count) {
            $section = [
                'conditions' => [
                    'item_types' => [$type],
                ],
                'item_count' => $count,
                'name' => $this->convertItemTypeToName($type),
                'score' => empty($fields['scores'][$type]) ? 0 : $fields['scores'][$type],
            ];

            if (isset($fields['choiceScore'][$type])) {
                $section['choiceScore'] = $fields['choiceScore'][$type];
            }

            if (!empty($fields['scoreType'][$type])) {
                $section['scoreType'] = $fields['scoreType'][$type];
            }

            if ('difficulty' == $fields['mode']) {
                $section['conditions']['distribution'] = $fields['percentages'];
            }

            $sections[] = $section;
        }

        return [$range, $sections];
    }

    protected function setSectionQuestionScore($sections, $assessmentType)
    {
        foreach ($sections as &$section) {
            foreach ($section['items'] as &$item) {
                foreach ($item['questions'] as &$question) {
                    $question['score'] = $section['score'];
                    $scoreType = empty($section['scoreType']) ? 'question' : $section['scoreType'];
                    $otherScore = empty($section['choiceScore']) ? 0 : $section['choiceScore'];
                    if ('text' == $question['answer_mode']) {
                        $question['score'] = 'question' == $scoreType ? $otherScore : $otherScore * count($question['answer']);
                    }
                    if ('material' == $section['conditions']['item_types'][0] && 'regular' != $assessmentType) {
                        $score = $section['score'] / $item['question_num'];
                        $question['score_rule']['score'] = ceil($score * 100) / 100;
                        $question['score'] = $question['score_rule']['score'];
                    } else {
                        $question['score_rule']['score'] = $question['score'];
                    }
                    if (in_array($question['answer_mode'], ['choice', 'uncertain_choice'])) {
                        $question['miss_score'] = $otherScore;
                    }
                    if (!empty($section['miss_score'])) {
                        $question['miss_score'] = $section['miss_score'];
                    }
                }
            }
        }

        return $sections;
    }

    private function convertItemTypeToName($type)
    {
        return [
            'single_choice' => '单选题',
            'choice' => '多选题',
            'uncertain_choice' => '不定项选择题',
            'fill' => '填空题',
            'determine' => '判断题',
            'essay' => '问答题',
            'material' => '材料题',
        ][$type];
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->biz->service('ItemBank:Assessment:AssessmentService');
    }

    protected function getItemCategoryService()
    {
        return $this->biz->service('ItemBank:Item:ItemCategoryService');
    }
}
