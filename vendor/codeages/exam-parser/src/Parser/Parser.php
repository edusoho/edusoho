<?php

namespace ExamParser\Parser;

use ExamParser\Parser\QuestionType\QuestionTypeFactory;

class Parser
{
    const START_SINGLE = '【导入开始】';

    const MATERIAL_START_SIGNAL = '【材料题开始】';

    const CODE_MATERIAL_START_SIGNAL = '<#材料题开始#>';

    const MATERIAL_END_SIGNAL = '【材料题结束】';

    const CODE_MATERIAL_END_SIGNAL = '<#材料题结束#>';

    const CODE_MATERIAL_SUB_QUESTION_START = '<#材料题子题#>';

    const UNCERTAIN_CHOICE_SIGNAL = '【不定项选择题】';

    const CODE_UNCERTAIN_CHOICE_SIGNAL = '<#不定项选择题#>';

    protected $body = '';

    protected $questions = array();

    public function __construct($body, $options = array())
    {
        $this->body = $body;
    }

    public function getQuestions()
    {
        return $this->questions;
    }

    public function parser()
    {
        $content = $this->filterStartSignal();
        $content = $this->filterMaterialSignal($content);
        $questionsArray = $this->resolveContent($content);
        $questions = array();
        foreach ($questionsArray as $question) {
            $this->matchQuestion($question);
        }

        return $this->questions;
    }

    /**
     * @return string
     */
    protected function filterStartSignal()
    {
        $bodyArray = explode(PHP_EOL.self::START_SINGLE.PHP_EOL, $this->body);
        if (2 == count($bodyArray)) {
            return $bodyArray[1];
        }

        return $this->body;
    }

    protected function filterMaterialSignal($content)
    {
        $pattern = '/'.PHP_EOL."{0,1}【材料题开始】[\s\S]*?【材料题结束】".PHP_EOL.'/';
        $content = preg_replace_callback(
            $pattern,
            function ($matches) {
                $str = preg_replace('/【材料题开始】\s*/', '<#材料题开始#>'.PHP_EOL, $matches[0]);
                $str = preg_replace('/\s*【材料题结束】/', PHP_EOL.'<#材料题结束#>', $str);
                $pattern = '/'.PHP_EOL.'{2,}/';
                $str = preg_replace($pattern, PHP_EOL.'<#材料题子题#>', $str);

                return $str;
            },
            $content);

        return $content;
    }

    protected function resolveContent($content)
    {
        $pattern = '/'.PHP_EOL.'{2,}/';
        $contentArray = preg_split($pattern, $content, -1, PREG_SPLIT_NO_EMPTY);
        $index = 0;
        foreach ($contentArray as $elem) {
            $questionArray[$index] = $elem;
            ++$index;
        }

        return $questionArray;
    }

    public function matchQuestion($questionStr)
    {
        $questionStr = trim($questionStr);
        $lines = explode(PHP_EOL, $questionStr);
        $lines = preg_replace('/^(答案|参考答案|正确答案|\[答案\]|\[参考答案\]|\[正确答案\]|【答案】|【正确答案】|【参考答案】)(：|:|)/', '<#答案#>', $lines);
        $lines = preg_replace('/^(难度|\[难度\]|【难度】)/', '<#难度#>', $lines);
        $lines = preg_replace('/^(分数|\[分数\]|【分数】)/', '<#分数#>', $lines);
        $lines = preg_replace('/^(解析|\[解析\]|【解析】)/', '<#解析#>', $lines);
        $lines = preg_replace('/^([A-J])(\.|、|。)/', '<#$1#>', $lines, -1, $count);
        $lines = preg_replace('/(\(正确\)|（正确）)\s{0,}/', '<#正确#>', $lines);
        $lines = preg_replace('/(\(错误\)|（错误）)\s{0,}/', '<#错误#>', $lines);
        $lines = preg_replace('/【不定项选择题】/', '<#不定项选择题#>', $lines);

        if (0 === strpos(trim($lines[0]), self::CODE_MATERIAL_START_SIGNAL)) {
            $type = 'material';
        } elseif (0 == $count) {
            if (preg_match('/\[\[(\S|\s)*?\]\]/', $lines[0])) {
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
        $this->questions[] = $questionType->convert($lines);
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
