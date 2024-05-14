<?php

namespace ApiBundle\Api\Resource\Ai;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\AI\Service\AIService;
use Biz\Question\QuestionException;
use Biz\User\UserException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AiGenerate extends AbstractResource
{
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
        $prompt = $this->makePrompt($item, $question);
        if ('blocking' == $params['responseMode']) {
            return $this->createBlockedResponse($prompt);
        }

        return $this->createStreamedResponse($prompt);
    }

    private function generateQuestionAnalysisForTeacher($params)
    {

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

    private function makePrompt($item, $question)
    {
        if (in_array($item['type'], ['single_choice', 'uncertain_choice', 'choice'])) {
            $answer = implode($question['answer']);
            $options = '';
            $key = 'single_choice' === $item['type'] ? 'radio' : 'checkbox';
            foreach (array_column($question['response_points'], $key) as $responsePoint) {
                $options .= "{$responsePoint['val']}.{$responsePoint['text']}\n";
            }

            return "有一道选择题，题干内容是： {{$question['stem']}}\n有以下选项：\n{$options}\n正确答案是{$answer}。\n假设你是该题的出题人，请根据以上内容为这道选择题生成解析，解析的长度不要超出500字符，在你的解析中，只有选项{$answer}是正确的，其他选项都是错误的，请不要出现其他选项是正确的这样的表述。";
        }
        if ('determine' == $item['type']) {
            $answer = 'T' == $question['answer'][0] ? '正确' : '错误';

            return "有一道判断题，题干内容是：{{$question['stem']}}\n答案是{$answer}。假设你是该题的出题人，请根据以上内容为这道判断题生成解析，解析的长度不要超出500字符。";
        }
        //填空题
        if ('fill' == $item['type']) {
            return "有一道填空题，题干内容是：{{$question['stem']}}\n";
        }
        //问答题
        if ('essay' == $item['type']) {
            return "有一道问答题，题干内容是：{{$question['stem']}}\n正确答案是{$question['answer'][0]}。假设你是该题的出题人，请根据以上内容为这道判断题生成解析，解析的长度不要超出500字符。";
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
