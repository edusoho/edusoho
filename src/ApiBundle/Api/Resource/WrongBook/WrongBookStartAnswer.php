<?php

namespace ApiBundle\Api\Resource\WrongBook;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\Assessment\AssessmentFilter;
use AppBundle\Common\ArrayToolkit;
use Biz\WrongBook\Dao\WrongQuestionCollectDao;
use Biz\WrongBook\Service\WrongQuestionService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Exception\AssessmentException;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

class WrongBookStartAnswer extends AbstractResource
{
    /**
     * @param $poolId
     */
    public function add(ApiRequest $request, $poolId)
    {
        $pool = $this->getWrongQuestionService()->getPool($poolId);
        $wrongQuestionsCount = $this->getCollectDao()->count(['pool_id' => $poolId, 'status' => 'wrong']);
        list($orderBy, $start) = $this->getSearchFields($wrongQuestionsCount);
        $wrongQuestions = $this->getCollectDao()->search(['pool_id' => $poolId, 'status' => 'wrong'], $orderBy, $start, 20);
        $itemIds = ArrayToolkit::column($wrongQuestions, 'item_id');
        $items = $this->getItemService()->findItemsByIds($itemIds, true);
        $answerScene = $this->initScene($pool);
        $assessment = [
            'name' => '错题练习',
            'displayable' => 0,
            'description' => '',
            'bank_id' => 0,
            'sections' => [
                [
                    'name' => '作业题目',
                    'items' => $items,
                ],
            ],
        ];

        $assessment = $this->getAssessmentService()->createAssessment($assessment);

        $this->getAssessmentService()->openAssessment($assessment['id']);

        $answerRecord = $this->getAnswerService()->startAnswer($answerScene['id'], $assessment['id'], $this->getCurrentUser()['id']);

        $assessment = $this->getAssessmentService()->showAssessment($answerRecord['assessment_id']);

        if (empty($assessment)) {
            throw AssessmentException::ASSESSMENT_NOTEXIST();
        }
        if ('open' !== $assessment['status']) {
            throw AssessmentException::ASSESSMENT_NOTOPEN();
        }

        $assessmentFilter = new AssessmentFilter();
        $assessmentFilter->filter($assessment);

        return [
            'assessment' => $assessment,
            'assessment_response' => $this->getAnswerService()->getAssessmentResponseByAnswerRecordId($answerRecord['id']),
            'answer_scene' => $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']),
            'answer_record' => $answerRecord,
        ];
    }

    protected function getSearchFields($count)
    {
        $regularWrongTimes = [
            ['wrong_times' => 'DESC'],
            ['wrong_times' => 'ASC'],
        ];
        $regularUpdatedTime = [
            ['last_submit_time' => 'DESC'],
            ['last_submit_time' => 'ASC'],
        ];
        $regularEmpty = [
            [],
            [],
        ];

        $orderBys = [$regularWrongTimes, $regularUpdatedTime, $regularEmpty];
        $orderBy = $orderBys[mt_rand(0, 2)][mt_rand(0, 1)];

        if ($count > 20) {
            $start = mt_rand(0, $count - 20);
        } else {
            $start = 0;
        }

        return [
            $orderBy,
            $start,
        ];
    }

    protected function initScene($pool)
    {
        if (empty($pool['scene_id'])) {
            $answerScene = $this->getAnswerSceneService()->create([
                'name' => '错题练习',
                'limited_time' => 0,
                'do_times' => 0,
                'redo_interval' => 0,
                'need_score' => 0,
                'manual_marking' => 0,
                'start_time' => 0,
            ]);
            $this->getWrongQuestionService()->updatePool($pool['id'], ['scene_id' => $answerScene['id']]);
        } else {
            $answerScene = $this->getAnswerSceneService()->get($pool['scene_id']);
        }

        return $answerScene;
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->getBiz()->service('WrongBook:WrongBookAssessmentService');
    }

    /**
     * @return AnswerService
     */
    protected function getAnswerService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerService');
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return WrongQuestionService
     */
    protected function getWrongQuestionService()
    {
        return $this->getBiz()->service('WrongBook:WrongQuestionService');
    }

    /**
     * @return WrongQuestionCollectDao
     */
    protected function getCollectDao()
    {
        return $this->getBiz()->dao('WrongBook:WrongQuestionCollectDao');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->getBiz()->service('ItemBank:Item:ItemService');
    }
}
