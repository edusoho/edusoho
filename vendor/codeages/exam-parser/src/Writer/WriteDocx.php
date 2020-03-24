<?php

namespace ExamParser\Writer;

use ExamParser\Exception\ExamException;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class WriteDocx
{
    protected $filename;

    /**
     * @var \PhpOffice\PhpWord\Element\Section
     */
    protected $section;

    /**
     * @var \PhpOffice\PhpWord\Element\TextRun
     */
    protected $textRun;

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

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($this->filename);
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

        if ('material' != $type) {
            $this->writeCommonQuestionText($question);
        }

        if (empty($question['isSub'])) {
            $this->section->addTextBreak();
        }
    }

    protected function buildSingleChoice($question)
    {
        if ('single_choice' != $question['type']) {
            return;
        }

        $this->writeStem($question['num'], $question['stem']);
        $this->writeOptions($question['options']);

        $this->writeText("【答案】{$question['answer']}");
    }

    protected function buildChoice($question)
    {
        if ('choice' != $question['type']) {
            return;
        }

        $this->writeStem($question['num'], $question['stem']);
        $this->writeOptions($question['options']);

        $this->writeText("正确答案：{$question['answer']}");
    }

    protected function buildUncertainChoice($question)
    {
        if ('uncertain_choice' != $question['type']) {
            return;
        }

        $this->writeText('【不定项选择题】');
        $this->writeStem($question['num'], $question['stem']);
        $this->writeOptions($question['options']);

        $this->writeText("正确答案：{$question['answer']}");
    }

    protected function buildFill($question)
    {
        if ('fill' != $question['type']) {
            return;
        }

        $this->writeStem($question['num'], $question['stem']);
    }

    protected function buildDetermine($question)
    {
        if ('determine' != $question['type']) {
            return;
        }

        $this->writeStem($question['num'], $question['stem'], $question['answer']);
    }

    protected function buildEssay($question)
    {
        if ('essay' != $question['type']) {
            return;
        }

        $this->writeStem($question['num'], $question['stem']);

        $this->useTextRun();
        $this->writeText('【答案】');
        foreach ($question['answer'] as $item) {
            $this->writeIn($item['element'], $item['content']);
        }
        $this->cancelTextRun();
    }

    protected function buildMaterial($question)
    {
        if ('material' != $question['type']) {
            return;
        }

        $this->writeText('【材料题开始】');

        $this->writeStem($question['num'], $question['stem']);
        $this->writeCommonQuestionText($question);

        foreach ($question['subs'] as $subQuestion) {
            $this->section->addTextBreak();
            $this->buildQuestionText($subQuestion['type'], $subQuestion);
        }

        $this->writeText('【材料题结束】');
    }

    protected function writeStem($seq, $stem, $answer = '')
    {
        $this->useTextRun();
        $this->writeText($seq);
        foreach ($stem as $item) {
            $this->writeIn($item['element'], $item['content']);
        }
        if (!empty($answer)) {
            $this->writeText("（{$answer}）");
        }
        $this->cancelTextRun();
    }

    protected function writeOptions(array $options)
    {
        foreach ($options as $option) {
            $this->useTextRun();
            foreach ($option as $item) {
                $this->writeIn($item['element'], $item['content']);
            }
            $this->cancelTextRun();
        }
    }

    protected function writeCommonQuestionText($question)
    {
        if (!empty($question['difficulty'])) {
            $this->writeText("【难度】{$question['difficulty']}");
        }
        if (!empty($question['score'])) {
            $this->writeText("【分数】{$question['score']}");
        }
        if (!empty($question['analysis'])) {
            $this->useTextRun();
            $this->writeText('【解析】');
            foreach ($question['analysis'] as $item) {
                $this->writeIn($item['element'], $item['content']);
            }
            $this->cancelTextRun();
        }
    }

    protected function useTextRun()
    {
        $this->textRun = $this->section->addTextRun();
    }

    protected function cancelTextRun()
    {
        $this->textRun = null;
    }

    protected function writeIn($element, $content)
    {
        $method = 'write'.ucfirst($element);
        $this->$method($content);
    }

    protected function writeImg($src)
    {
        if (empty($this->textRun)) {
            $this->section->addImage($src);
        } else {
            $this->textRun->addImage($src);
        }
    }

    protected function writeText($text)
    {
        $text = strip_tags($text);
        $text = str_replace(array("\n", "\r", "\t"), '<w:br/>', $text);
        $text = str_replace('&', '&amp;', $text);
        $text = str_replace(array('&amp;nbsp;', '&amp;gt;', '&amp;lt;', '&amp;amp;', '&amp;quot;', '&amp;apos;', '&amp;divide;', '&amp;#39;', '&amp;rdquo;', '&amp;middot;', '&amp;rsquo;', '&amp;lsquo;', '&amp;hellip;', '&amp;mdash;'), array('&nbsp;', '&gt;', '&lt;', '&amp;', '&quot;', '&apos;', '&divide;', '&#39;', '”', '·', '’', '‘', '…', '—'), $text);

        $text = trim($text);

        if (empty($text)) {
            return;
        }

        if (empty($this->textRun)) {
            $this->section->addText($text);
        } else {
            $this->textRun->addText($text);
        }
    }
}
