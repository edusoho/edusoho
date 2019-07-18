<?php

namespace ExamParser\Writer;

use ExamParser\Exception\ExamException;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class WriteDocx
{
    protected $filename;

    protected $section;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function write($questions)
    {
        $phpWord = new PhpWord();

        $this->section = $phpWord->addSection();

        foreach ($questions as $question) {
            $this->buildQuestionText($question['type'], $question);
        }

        $this->writeIn('【导出结束】');

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save('/tmp/'.$this->filename.'.docx');
    }

    protected function buildQuestionText($type, $question)
    {
        $types = array(
            'single_choice' => 'buildSingleChoice',
            'choice' => 'buildChoice',
            'uncertain_choice' => 'buildUncertainChoice',
            'fill' => 'buildFill',
            'determine' => 'buildDetermine',
            'essay' => 'buildEssay',
            'material' => 'buildMaterial',
        );

        if (!in_array($type, array_keys($types))) {
            throw new ExamException('not found question type');
        }

        $method = $types[$type];

        $this->$method($question);

        $this->section->addLine();
    }

    protected function buildSingleChoice($question)
    {
        if ('single_choice' != $question['type']) {
            return;
        }

        $this->writeIn("{$question['seq']}{$question['stem']}");

        foreach ($question['choices'] as $choice) {
            $this->writeIn("{$choice}");
        }

        $this->writeIn("【答案】{$question['answer']}");

        if (!empty($question['analysis'])) {
            $this->writeIn("【解析】{$question['analysis']}");
        }
    }

    protected function buildChoice($question)
    {
        if ('choice' != $question['type']) {
            return;
        }

        $this->writeIn("{$question['seq']}{$question['stem']}");

        foreach ($question['choices'] as $choice) {
            $this->writeIn("{$choice}");
        }

        $this->writeIn("正确答案：{$question['answer']}");

        if (!empty($question['analysis'])) {
            $this->writeIn("【解析】{$question['analysis']}");
        }
    }

    protected function buildUncertainChoice($question)
    {
        if ('uncertain_choice' != $question['type']) {
            return;
        }

        $this->writeIn("{$question['seq']}{$question['stem']}");

        foreach ($question['choices'] as $choice) {
            $this->writeIn("{$choice}");
        }

        $this->writeIn("正确答案：{$question['answer']}");

        if (!empty($question['analysis'])) {
            $this->writeIn("【解析】{$question['analysis']}");
        }
    }

    protected function buildFill($question)
    {
        if ('fill' != $question['type']) {
            return;
        }

        $this->writeIn("{$question['seq']}{$question['stem']}");
    }

    protected function buildDetermine($question)
    {
        if ('determine' != $question['type']) {
            return;
        }

        $this->writeIn("{$question['seq']}{$question['stem']}（{$question['answer']}）");
    }

    protected function buildEssay($question)
    {
        if ('essay' != $question['type']) {
            return;
        }

        $this->writeIn("{$question['seq']}{$question['stem']}");
    }

    protected function buildMaterial($question)
    {
        if ('material' != $question['type']) {
            return;
        }

        $this->writeIn('【材料题开始】');

        $this->writeIn("{$question['seq']}{$question['stem']}");
        $this->section->addLine();

        foreach ($question['subs'] as $subQuestion) {
            $this->buildQuestionText($subQuestion['type'], $subQuestion);
        }

        $this->writeIn('【材料题结束】');
    }

    protected function writeIn($questionText)
    {
        $this->section->addText($questionText);
    }
}
