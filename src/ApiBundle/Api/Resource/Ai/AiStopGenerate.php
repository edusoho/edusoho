<?php

namespace ApiBundle\Api\Resource\Ai;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\AI\Constant\AIApp;
use Biz\AI\Service\AIService;
use Biz\Common\CommonException;
use Biz\Question\Traits\QuestionAnswerModeTrait;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

class AiStopGenerate extends AbstractResource
{
    use QuestionAnswerModeTrait;

    public function add(ApiRequest $request, $type)
    {
        $params = $request->request->all();
        if ('question_analysis' == $type) {
            $app = $this->getAIApp($params);
            $this->getAIService()->stopGeneratingAnswer($app, $params['messageId'], $params['taskId']);
        }

        return ['ok' => true];
    }

    private function getAIApp($params)
    {
        if (!empty($params['questionId'])) {
            $question = $this->getItemService()->getQuestionIncludeDeleted($params['questionId']);
            $item = $this->getItemService()->getItemIncludeDeleted($question['item_id']);
            $type = 'material' == $item['type'] ? "material-{$this->modeToType[$question['answer_mode']]}" : $item['type'];
        }
        if (!empty($params['questionType'])) {
            $type = $params['questionType'];
        }
        if (empty($type)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        return [
            'single_choice' => AIApp::CHOICE_QUESTION_GENERATE_ANALYSIS,
            'uncertain_choice' => AIApp::CHOICE_QUESTION_GENERATE_ANALYSIS,
            'choice' => AIApp::CHOICE_QUESTION_GENERATE_ANALYSIS,
            'determine' => AIApp::DETERMINE_QUESTION_GENERATE_ANALYSIS,
            'fill' => AIApp::FILL_QUESTION_GENERATE_ANALYSIS,
            'essay' => AIApp::ESSAY_QUESTION_GENERATE_ANALYSIS,
            'material-single_choice' => AIApp::MATERIAL_CHOICE_QUESTION_GENERATE_ANALYSIS,
            'material-uncertain_choice' => AIApp::MATERIAL_CHOICE_QUESTION_GENERATE_ANALYSIS,
            'material-choice' => AIApp::MATERIAL_CHOICE_QUESTION_GENERATE_ANALYSIS,
            'material-determine' => AIApp::MATERIAL_DETERMINE_QUESTION_GENERATE_ANALYSIS,
            'material-fill' => AIApp::MATERIAL_FILL_QUESTION_GENERATE_ANALYSIS,
            'material-essay' => AIApp::MATERIAL_ESSAY_QUESTION_GENERATE_ANALYSIS,
        ][$type];
    }

    /**
     * @return AIService
     */
    private function getAIService()
    {
        return $this->service('AI:AIService');
    }

    /**
     * @return ItemService
     */
    private function getItemService()
    {
        return $this->service('ItemBank:Item:ItemService');
    }
}
