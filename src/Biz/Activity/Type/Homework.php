<?php

namespace Biz\Activity\Type;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Config\Activity;
use Biz\Activity\Service\HomeworkActivityService;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\Testpaper\TestpaperException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentSectionItemService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\Item\Service\ItemCategoryService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

class Homework extends Activity
{
    protected function registerListeners()
    {
        return [];
    }

    public function get($targetId)
    {
        $homework = $this->getHomeworkActivityService()->get($targetId);
        if ($homework) {
            $homework['assessment'] = $this->getAssessmentService()->getAssessment($homework['assessmentId']);
            if (empty($homework['has_published'])) {
                $homeworks = $this->getHomeworkActivityService()->findByAssessmentId($homework['assessmentId']);
                $homework['has_published'] = in_array(1, ArrayToolkit::column($homeworks, 'has_published'));
            }
        }
        if (isset($homework['has_published']) && empty($homework['has_published'])) {
            $questions = $homework['assessment'] ? $this->getSectionItemService()->findSectionItemDetailByAssessmentId($homework['assessment']['id']) : [];
            $questionBank = $categories = [];
            if ($questions) {
                $itemBankIds = array_unique(ArrayToolkit::column($questions, 'bank_id'));
                $questionBank = $this->getQuestionBankService()->getQuestionBankByItemBankId(array_shift($itemBankIds));
            }
            if ($questionBank) {
                $categories = $this->getItemCategoryService()->findItemCategoriesByBankId($questionBank['itemBankId']);
                $categories = ArrayToolkit::index($categories, 'id');
            }
            $extData = [
                'questionBank' => $questionBank,
                'categories' => $categories,
                'questions' => $questions,
            ];
            $homework = array_merge($homework, $extData);
        }

        return $homework;
    }

    public function find($targetIds, $showCloud = 1)
    {
        return $this->getHomeworkActivityService()->findByIds($targetIds);
    }

    public function create($fields)
    {
        try {
            $this->getBiz()['db']->beginTransaction();

            $answerScene = $this->getAnswerSceneService()->create([
                'name' => $fields['title'],
                'limited_time' => 0,
                'do_times' => 0,
                'redo_interval' => 0,
                'need_score' => $fields['finishType'] == 'score' ? 1 : 0,
                'manual_marking' => 1,
                'start_time' => 0,
            ]);

            $assessment = $this->createAssessment($fields['title'], $fields);
            $activity = $this->getHomeworkActivityService()->create([
                'answerSceneId' => $answerScene['id'],
                'assessmentId' => $assessment['id'],
            ]);

            $this->getBiz()['db']->commit();

            return $activity;
        } catch (\Exception $e) {
            $this->getBiz()['db']->rollback();
            throw $e;
        }
    }

    protected function createAssessment($name, $fields)
    {
        $items = $this->getItemService()->findItemsByIds($fields['questionIds'], true);
        $items = $this->processItemQuestions($items, $fields);
        $bankIds = array_column($items, 'bank_id');
        $assessment = [
            'bank_id' => array_shift($bankIds),
            'name' => $name,
            'description' => $fields['description'],
            'displayable' => 0,
            'sections' => [
                [
                    'name' => '作业题目',
                    'items' => $items,
                ],
            ],
        ];

        $assessment = $this->getAssessmentService()->createAssessment($assessment);

        return $this->getAssessmentService()->openAssessment($assessment['id']);
    }

    protected function processItemQuestions($items, $fields)
    {
        $scoreArr = $fields['score'];
        $scoreTypeArr = $fields['scoreType'];
        $choiceScoreArr = $fields['choiceScore'];
        foreach ($items as &$item) {
            $questions = $item['questions'];
            foreach ($questions as &$question) {
                $score = empty($scoreArr[$question['id']]) ? 0 : $scoreArr[$question['id']];
                if ('text' == $question['answer_mode']) {
                    $score = 'question' == $scoreTypeArr[$question['id']] ? $choiceScoreArr[$question['id']] : $choiceScoreArr[$question['id']] * count($question['answer']);
                }
                $question['score'] = $score;
                $question['score_rule'] = [
                    'score' => $score,
                    'scoreType' => empty($scoreTypeArr[$question['id']]) ? 'question' : $scoreTypeArr[$question['id']],
                    'otherScore' => empty($choiceScoreArr[$question['id']]) ? 0 : $choiceScoreArr[$question['id']],
                ];
                if (in_array($question['answer_mode'], ['choice', 'uncertain_choice']) && 'question' == $scoreTypeArr[$question['id']]) {
                    $question['miss_score'] = $choiceScoreArr[$question['id']];
                }
            }
            $item['questions'] = $questions;
        }

        return $items;
    }

    public function copy($activity, $config = [])
    {
        $homework = $this->get($activity['mediaId']);

        try {
            $this->getBiz()['db']->beginTransaction();

            $answerScene = $this->getAnswerSceneService()->get($homework['answerSceneId']);
            $answerScene = $this->getAnswerSceneService()->create([
                'name' => $answerScene['name'],
                'limited_time' => 0,
                'do_times' => 0,
                'redo_interval' => 0,
                'need_score' => $activity['finishType'] == 'score' ? 1 : 0,
                'manual_marking' => 1,
                'start_time' => 0,
            ]);

            $activity = $this->getHomeworkActivityService()->create([
                'answerSceneId' => $answerScene['id'],
                'assessmentId' => $homework['assessmentId'],
                'has_published' => 1,
            ]);

            $this->getBiz()['db']->commit();

            return $activity;
        } catch (\Exception $e) {
            $this->getBiz()['db']->rollback();
            throw $e;
        }
    }

    public function sync($sourceActivity, $activity)
    {
        $sourceHomework = $this->get($sourceActivity['mediaId']);
        $homework = $this->get($activity['mediaId']);

        $fields = [
            'name' => $sourceHomework['assessment']['name'],
            'description' => $sourceHomework['assessment']['description'],
        ];

        $this->getAssessmentService()->updateBasicAssessment($homework['assessmentId'], $fields);

        return $homework;
    }

    public function update($targetId, &$fields, $activity)
    {
        $homework = $this->getHomeworkActivityService()->get($targetId);
        if (!$homework) {
            throw TestpaperException::NOTFOUND_TESTPAPER();
        }
        $accessment = [
            'name' => $fields['title'],
            'description' => $fields['description'],
        ];
        if (!empty($fields['questionIds'])) {
            $items = $this->getItemService()->findItemsByIds($fields['questionIds'], true);
            $items = $this->processItemQuestions($items, $fields);
            $bankIds = array_column($items, 'bank_id');
            $accessment['bank_id'] = array_shift($bankIds);
            $accessment['sections'] = [
                [
                    'name' => '作业题目',
                    'items' => $items,
                ],
            ];
        }

        $answerScene =$this->getAnswerSceneService()->get($homework['answerSceneId']);
        $answerScene['need_score'] = $fields['finishType'] == 'score' ? 1 : 0;
        $this->getAnswerSceneService()->update($homework['answerSceneId'],$answerScene);
        $this->getAssessmentService()->updateAssessment($homework['assessmentId'], $accessment);

        return $homework;
    }

    public function delete($targetId)
    {
        return $this->getHomeworkActivityService()->delete($targetId);
    }

    public function isFinished($activityId)
    {
        $user = $this->getCurrentUser();

        $activity = $this->getActivityService()->getActivity($activityId, true);

        $answerRecord = $this->getAnswerRecordService()->getLatestAnswerRecordByAnswerSceneIdAndUserId(
            $activity['ext']['answerSceneId'],
            $user['id']
        );

        if (empty($answerRecord)) {
            return false;
        }
        if ('submit' === $activity['finishType'] && in_array($answerRecord['status'], [AnswerService::ANSWER_RECORD_STATUS_REVIEWING, AnswerService::ANSWER_RECORD_STATUS_FINISHED])) {
            return true;
        }
        $answerReport = $this->getAnswerReportService()->getSimple($answerRecord['answer_report_id']);
        if (AnswerService::ANSWER_RECORD_STATUS_FINISHED == $answerRecord['status'] && 'score' === $activity['finishType'] && $answerReport['score'] >= $activity['finishData']) {
            return true;
        }

        return false;
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->getBiz()->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->getBiz()->service('ItemBank:Item:ItemService');
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return HomeworkActivityService
     */
    protected function getHomeworkActivityService()
    {
        return $this->getBiz()->service('Activity:HomeworkActivityService');
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->getBiz()->service('QuestionBank:QuestionBankService');
    }

    /**
     * @return ItemCategoryService
     */
    protected function getItemCategoryService()
    {
        return $this->getBiz()->service('ItemBank:Item:ItemCategoryService');
    }

    /**
     * @return AssessmentSectionItemService
     */
    protected function getSectionItemService()
    {
        return $this->getBiz()->service('ItemBank:Assessment:AssessmentSectionItemService');
    }

    /**
     * @return AnswerReportService
     */
    protected function getAnswerReportService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerReportService');
    }
}
