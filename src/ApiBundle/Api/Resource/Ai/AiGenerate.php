<?php

namespace ApiBundle\Api\Resource\Ai;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\AI\Service\AIService;
use Biz\Question\QuestionException;
use Biz\Question\Traits\QuestionAnswerModeTrait;
use Biz\User\UserException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AiGenerate extends AbstractResource
{
    use QuestionAnswerModeTrait;

    public function add(ApiRequest $request, $type)
    {
        if ('question_analysis' == $type) {
            return $this->generateQuestionAnalysis($request->request->all());
        }

        return [];
    }

    private function generateQuestionAnalysis($params)
    {
        if (!$this->getCurrentUser()->isLogin()) {
            throw UserException::UN_LOGIN();
        }
        if ('student' == $params['role']) {
            return $this->generateQuestionAnalysisForStudent($params);
        }
        if ('teacher' == $params['role']) {
            return $this->generateQuestionAnalysisForTeacher($params);
        }

        return [];
    }

    private function generateQuestionAnalysisForStudent($params)
    {
        $answerRecord = $this->getAnswerRecordService()->get($params['answerRecordId']);
        if (empty($answerRecord) || $this->getCurrentUser()->getId() != $answerRecord['user_id']) {
            throw UserException::PERMISSION_DENIED();
        }
        $question = $this->getItemService()->getQuestion($params['questionId']);
        if (empty($question)) {
            throw QuestionException::NOTFOUND_QUESTION();
        }
        $item = $this->getItemService()->getItemIncludeDeleted($question['item_id']);
        //todo 校验answerRecordId和questionId是否匹配
        $question['material'] = $item['material'];
        $inputs = $this->makeInputsFromQuestion($item['type'], $question);
        $prompt = $this->makePrompt($item['type'], $inputs);
        if ('blocking' == $params['responseMode']) {
            return $this->createBlockedResponse($prompt);
        }

        return $this->createStreamedResponse($prompt);
    }

    private function generateQuestionAnalysisForTeacher($params)
    {
        if (!$this->getCurrentUser()->isTeacher() && !$this->getCurrentUser()->isAdmin()) {
            return [];
        }
        $inputs = $this->makeInputsFromTeacherInput($params['type'], $params);
        $prompt = $this->makePrompt($params['type'], $inputs);

        return $this->createStreamedResponse($prompt);
    }

    private function createBlockedResponse($prompt)
    {
        ob_start();
        $this->getAIService()->generateAnswer($prompt);
        $response = ob_get_clean();
        $answer = '';
        foreach (array_filter(explode("\n\n", $response)) as $slice) {
            $data = json_decode(substr($slice, 6), true);
            if ('message' == $data['event']) {
                $answer .= $data['answer'];
            }
        }

        return ['answer' => $answer];
    }

    private function createStreamedResponse($prompt)
    {
        $aiService = $this->getAIService();

        return new StreamedResponse(
            function () use ($aiService, $prompt) {
                $aiService->generateAnswer($prompt);
            },
            200,
            [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'Connection' => 'keep-alive',
                'X-Accel-Buffering' => 'no',
            ]
        );
    }

    private function makeInputsFromQuestion($type, $question)
    {
        if (in_array($type, ['single_choice', 'uncertain_choice', 'choice'])) {
            $options = '';
            $responsePoints = array_column($question['response_points'], 'single_choice' == $type ? 'radio' : 'checkbox');
            foreach ($responsePoints as $responsePoint) {
                $options .= "{$responsePoint['val']}.{$responsePoint['text']}\n";
            }

            return [
                'stem' => $question['stem'],
                'options' => $options,
                'answer' => implode($question['answer']),
            ];
        }
        if ('determine' == $type) {
            return [
                'stem' => $question['stem'],
                'answer' => 'T' == $question['answer'][0] ? '正确' : '错误',
            ];
        }
        if ('fill' == $type) {
            $answer = '';
            foreach ($question['answer'] as $key => $blankAnswer) {
                $blankAnswers = explode('|', $blankAnswer);
                $answer .= empty($answer) ? '' : ';';
                $answer .= 1 == '第'.($key + 1).'空的答案是'.count($blankAnswers) ? $blankAnswers[0] : implode('或', $blankAnswers);
            }

            return [
                'stem' => $question['stem'],
                'answer' => $answer,
            ];
        }
        if ('essay' == $type) {
            return [
                'stem' => $question['stem'],
                'answer' => $question['answer'][0],
            ];
        }
        if ('material' == $type) {
            $inputs = $this->makeInputsFromQuestion($this->modeToType[$question['answer_mode']], $question);
            $inputs['material'] = $question['material'];

            return $inputs;
        }
    }

    private function makeInputsFromTeacherInput($type, $params)
    {
        if (in_array($type, ['single_choice', 'uncertain_choice', 'choice'])) {
            return [
                'stem' => $params['stem'],
                'options' => implode("\n", $params['options']),
                'answer' => $params['answer'],
            ];
        }
        if ('determine' == $type) {
            return [
                'stem' => $params['stem'],
                'answer' => $params['answer'],
            ];
        }
        if ('fill' == $type) {
            $answer = '';
            foreach ($params['answers'] as $key => $blankAnswer) {
                $blankAnswers = explode('|', $blankAnswer);
                $answer .= empty($answer) ? '' : ';';
                $answer .= 1 == '第'.($key + 1).'空的答案是'.count($blankAnswers) ? $blankAnswers[0] : implode('或', $blankAnswers);
            }

            return [
                'stem' => $params['stem'],
                'answer' => $answer,
            ];
        }
        if ('essay' == $type) {
            return [
                'stem' => $params['stem'],
                'answer' => $params['answer'],
            ];
        }
        list($itemType, $questionType) = explode('-', $type);
        if ('material' == $itemType) {
            $inputs = $this->makeInputsFromTeacherInput($questionType, $params);
            $inputs['material'] = $params['material'];

            return $inputs;
        }
    }

    private function makePrompt($type, $inputs)
    {
        if (in_array($type, ['single_choice', 'uncertain_choice', 'choice'])) {
            return "有一道选择题，题干内容是： {{$inputs['stem']}}\n有以下选项：\n{$inputs['options']}\n正确答案是{$inputs['answer']}。\n假设你是该题的出题人，请根据以上内容为这道选择题生成解析，解析的长度不要超出500字符，在你的解析中，只有选项{$inputs['answer']}是正确的，其他选项都是错误的，请不要出现其他选项是正确的这样的表述。";
        }
        if ('determine' == $type) {
            return "有一道判断题，题干内容是：{{$inputs['stem']}}\n答案是{$inputs['answer']}。假设你是该题的出题人，请根据以上内容为这道判断题生成解析，解析的长度不要超出500字符。";
        }
        //填空题
        if ('fill' == $type) {
            return "有一道填空题，题干内容是：{{$inputs['stem']}}\n{$inputs['answer']}。假设你是该题的出题人，请根据以上内容为这道填空题生成解析，解析的长度不要超出500字符。";
        }
        //问答题
        if ('essay' == $type) {
            return "有一道问答题，题干内容是：{{$inputs['stem']}}\n正确答案是{$inputs['answer']}。假设你是该题的出题人，请根据以上内容为这道判断题生成解析，解析的长度不要超出500字符。";
        }
        //材料题
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->service('ItemBank:Item:ItemService');
    }

    /**
     * @return AIService
     */
    protected function getAIService()
    {
        return $this->service('AI:AIService');
    }
}
