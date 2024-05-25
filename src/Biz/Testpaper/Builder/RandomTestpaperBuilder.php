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
        $methodName = getSectionsBy.ucfirst($generateType);

        return $this->$methodName($fields);
    }

    private function getSectionsByQuestionType($fields)
    {
        list($range, $sections) = $this->getRangeAndSections($fields);
        return $this->getAssessmentService()->drawItems($range, $sections);
    }

    private function getSectionsByQuestionTypeCategory($fields)
    {
        // 这里需要重新调用整理数据
        list($range, $sections) = $this->getRangeAndSectionsByQuestionTypeCategory($fields);
        return $this->getAssessmentService()->drawItems($range, $sections);
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

    protected function getRangeAndSectionsByQuestionTypeCategory($fields)
    {
        $range = [
            'bank_id' => $fields['itemBankId'],
            'category_ids' => [],
        ];

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
