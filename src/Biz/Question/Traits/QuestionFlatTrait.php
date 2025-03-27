<?php

namespace Biz\Question\Traits;

trait QuestionFlatTrait
{
    use QuestionAnswerModeTrait;
    use ItemTypeChineseNameTrait;

    private function flattenMain($type, $question)
    {
        if ('material' == $type) {
            $content = "[材料] {$question['material']}  \n[{$this->chineseNames[$this->modeToType[$question['answer_mode']]]}] ";
        } else {
            $content = "[{$this->chineseNames[$type]}] ";
        }

        if (in_array($type, ['single_choice', 'choice', 'uncertain_choice'])) {
            $responsePoints = array_column($question['response_points'], 'radio') ?: array_column($question['response_points'], 'checkbox');
            $options = [];
            foreach ($responsePoints as $responsePoint) {
                $options[] = "{$responsePoint['val']}. {$responsePoint['text']}";
            }
            $content .= $question['stem']."  \n".implode("  \n", $options);
        }
        if (in_array($type, ['determine', 'essay'])) {
            $content .= $question['stem'];
        }
        if ('fill' == $type) {
            $content .= str_replace('[[]]', '__', $question['stem']);
        }

        return strip_tags($content);
    }

    private function flattenAnswer($type, $question)
    {
        if (in_array($type, ['single_choice', 'choice', 'uncertain_choice', 'essay'])) {
            $answer = implode('', $question['answer']);
        }
        if ('determine' == $type) {
            $answer = 'T' == $question['answer'][0] ? '正确' : '错误';
        }
        if ('fill' == $type) {
            $answer = str_replace('|', '或', implode(';', $question['answer']));
        }

        return empty($answer) ? '' : strip_tags("  \n[正确答案] {$answer}");
    }

    private function flattenAnalysis($question)
    {
        return empty($question['analysis']) ? '' : strip_tags("  \n[答案解析] {$question['analysis']}");
    }
}
