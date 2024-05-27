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
        $sections = $this->setSectionQuestionScore($sections);
        $assessment = [
            'bank_id' => $fields['itemBankId'],
            'name' => $fields['name'],
            'displayable' => 1,
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
        $generateType = $fields['generateType'] ?? 'questionType';
        $methodName = 'getSectionsBy'.ucfirst($generateType);

        return $this->$methodName($fields);
    }

    private function getSectionsByQuestionType($fields)
    {
        list($range, $sections) = $this->getRangeAndSections($fields);

        return $this->getAssessmentService()->drawItems($range, $sections);
    }

    private function getSectionsByQuestionTypeCategory($fields)
    {
        $itemsMerged = [];
        foreach ($fields['questionCategoryCounts'] as $questionCategoryCount) {
            list($range, $sections) = $this->getRangeAndSectionsByQuestionTypeCategory($fields, $questionCategoryCount);
            $drawItems = $this->getAssessmentService()->drawItems($range, $sections);
            $itemsMerged = $this->mergeItem($itemsMerged, $drawItems);
        }

        return $itemsMerged;
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
            $itemType = $item['conditions']['item_types'][0]; // 假设每个conditions中只有一个item_type

            return [$itemType => $item];
        }, $drawItems);
        foreach ($itemsMerged as $key => &$item) {
            $itemType = $item['conditions']['item_types'][0];
            $drawItem = $drawItemsMap[0][$itemType];
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
        foreach ($questionCategoryCount['sections'] as $type => $section) {
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

    protected function setSectionQuestionScore($sections)
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
                    $question['score_rule'] = [
                        'score' => $question['score'],
                        'scoreType' => $scoreType,
                        'otherScore' => $otherScore,
                    ];
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
