<?php

namespace ExamParser\Parser\QuestionType;

use ExamParser\Constants\QuestionElement;
use ExamParser\Parser\Parser;

class Material extends AbstractQuestion
{
    protected $subQuestions = array();

    public function convert($questionLines)
    {
        $question = array(
            'stem' => '',
            'type' => 'material',
            'score' => 2.0,
            'analysis' => '',
            'difficulty' => 'normal',
            'subQuestions' => array(),
        );

        $preNode = QuestionElement::STEM;

        $question['subQuestions'] = $this->filterSubQuestions($questionLines);

        foreach ($questionLines as $line) {
            if (Parser::CODE_MATERIAL_START_SIGNAL == trim($line) || Parser::CODE_MATERIAL_END_SIGNAL == trim($line)) {
                continue;
            }

            //处理难度
            if ($this->matchDifficulty($question, $line, $preNode)) {
                continue;
            }
            //处理分数
            if ($this->matchScore($question, $line, $preNode)) {
                continue;
            }

            //处理解析
            if ($this->matchAnalysis($question, $line, $preNode)) {
                continue;
            }

            if (QuestionElement::STEM == $preNode) {
                $question['stem'] .= (empty($question['stem']) ? '' : '<br/>').preg_replace('/^\d{0,5}(\.|、|。|\s)/', '', $line).PHP_EOL;
            }
        }

        return $question;
    }

    protected function filterSubQuestions(&$questionLines)
    {
        $subQuestions = array();
        $questionSeq = -1;
        foreach ($questionLines as $key => $line) {
            if (Parser::CODE_MATERIAL_START_SIGNAL == trim($line) || Parser::CODE_MATERIAL_END_SIGNAL == trim($line)) {
                continue;
            }
            if (0 === strpos(trim($line), Parser::CODE_MATERIAL_SUB_QUESTION_START)) {
                ++$questionSeq;
            }

            if ($questionSeq >= 0) {
                $subQuestions[$questionSeq][] = str_replace(Parser::CODE_MATERIAL_SUB_QUESTION_START, '', trim($line));
                unset($questionLines[$key]);
            }
        }
        foreach ($subQuestions as $lines) {
            $count = preg_match_all('/\<\#[A-Z]\#\>/', implode(PHP_EOL, $lines), $matches);
            if (0 === strpos(trim($lines[0]), Parser::CODE_MATERIAL_START_SIGNAL)) {
                $type = 'material';
            } elseif (0 == $count) {
                if (preg_match('/\[\[(\S|\s).*?\]\]/', $lines[0])) {
                    $type = 'fill';
                } elseif (preg_match('/(\<\#正确\#\>|\<\#错误\#\>)/', trim(implode('', $lines)))) {
                    $type = 'determine';
                } else {
                    $type = 'essay';
                }
            } else {
                $type = 'choice';
            }

            $questionType = QuestionTypeFactory::create($this->toCamelCase($type));
            $this->subQuestions[] = $questionType->convert($lines);
        }

        return $this->subQuestions;
    }

    //下划线命名到驼峰命名
    protected function toCamelCase($str)
    {
        $array = explode('_', $str);
        $result = '';
        $len = count($array);
        for ($i = 0; $i < $len; ++$i) {
            $result .= ucfirst($array[$i]);
        }

        return $result;
    }
}
