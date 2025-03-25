<?php

namespace Biz\Question\Traits;

trait QuestionFlatTrait
{
    private function flatten($item)
    {
        $typePart = [
            'single_choice' => '单选题',
            'choice' => '多选题',
            'uncertain_choice' => '不定项',
            'determine' => '判断题',
            'fill' => '填空题',
            'essay' => '问答题',
        ][$item['type']];

        if ('material' != $item['type']) {
            $question = $item['questions'][0];

            return "[$typePart] {$this->flattenStem($item, $question)}[正确答案] {$this->flattenAnswer($item, $question)}\n".($question['analysis'] ? "[答案解析] {$question['analysis']}" : '');
        }

        return "[材料题] ";
    }

    private function flattenStem($item, $question)
    {
        if (in_array($item['type'], ['determine', 'essay', 'fill'])) {
            return $question['stem']."\n";
        }
        if (in_array($item['type'], ['single_choice', 'choice', 'uncertain_choice'])) {
            $stem = $question['stem']."\n";
            $responsePoints = array_column($question['response_points'], 'radio') ?: array_column($question['response_points'], 'checkbox');
            foreach ($responsePoints as $responsePoint) {
                $stem .= "{$responsePoint['val']}. {$responsePoint['text']}\n";
            }

            return $stem;
        }

        return '';
    }

    private function flattenAnswer($item, $question)
    {
        if (in_array($item['type'], ['single_choice', 'choice', 'uncertain_choice', 'essay'])) {
            return implode('', $question['answer']);
        }
        if ('determine' == $item['type']) {
            return 'T' == $question['answer'][0] ? '正确' : '错误';
        }
        if ('fill' == $item['type']) {
            return implode(',', $question['answer']);
        }
    }
}
