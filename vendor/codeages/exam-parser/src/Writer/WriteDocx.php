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

        $this->writeDescription();
        $this->writeStartSignal();

        foreach ($questions as $question) {
            $this->buildQuestionText($question['type'], $question);
        }

        header('Content-Type: application/msword');
        header('Content-Disposition: attachment; filename='.$this->filename.'.docx');

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save('php://output');
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
        $this->writeCommonQuestionText($question);

        if (empty($question['isSub'])) {
            $this->section->addTextBreak();
        }
    }

    protected function buildSingleChoice($question)
    {
        if ('single_choice' != $question['type']) {
            return;
        }

        $this->writeStem($question['seq'], $question['stem']);
        $this->writeOptions($question['options']);

        $this->writeText("【答案】{$question['answer']}");
    }

    protected function buildChoice($question)
    {
        if ('choice' != $question['type']) {
            return;
        }

        $this->writeStem($question['seq'], $question['stem']);
        $this->writeOptions($question['options']);

        $this->writeText("正确答案：{$question['answer']}");
    }

    protected function buildUncertainChoice($question)
    {
        if ('uncertain_choice' != $question['type']) {
            return;
        }

        $this->writeText('【不定项选择题】');
        $this->writeStem($question['seq'], $question['stem']);
        $this->writeOptions($question['options']);

        $this->writeText("正确答案：{$question['answer']}");
    }

    protected function buildFill($question)
    {
        if ('fill' != $question['type']) {
            return;
        }

        $this->writeStem($question['seq'], $question['stem']);
    }

    protected function buildDetermine($question)
    {
        if ('determine' != $question['type']) {
            return;
        }

        $this->writeStem($question['seq'], $question['stem'], $question['answer']);
    }

    protected function buildEssay($question)
    {
        if ('essay' != $question['type']) {
            return;
        }

        $this->writeStem($question['seq'], $question['stem']);

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

        $this->writeStem($question['seq'], $question['stem']);

        foreach ($question['subs'] as $subQuestion) {
            $this->section->addTextBreak();
            $this->buildQuestionText($subQuestion['type'], $subQuestion);
        }

        $this->writeText('【材料题结束】');
    }

    protected function writeDescription()
    {
        $this->writeText('请仔细阅读以下导入说明，【导入开始】以后才是导入正文。');
        $this->writeText('1. 题号及选项编号不能使用word默认项目编号，可设置取消自动编号，路径如下：文件->选项->校对->自动更正选项->键入时自动套用格式->取消勾选“自动编号列表”；或复制全文，然后右击，选择无格式粘贴。');
        $this->writeText('2. 题型包括单项选择题，多项选择题，填空题，判断题，问答题和材料题；');
        $this->writeText('同一道题目之间不得有空行（材料题除外）。');
        $this->writeText('3. 题目导入图片时，注意不要产生空行；');
        $this->writeText('4. 选择题最多包含10个选项，多选题的答案连续填写，如“ABC”；');
        $this->writeText('5. 填空题以两个连续的中括号[[]]（注意是英文的中括号）代表空，如果某个空有多个备选答案，则每个答案之间用“|”隔开；');
        $this->writeText('6. 判断题的答案只能是“正确”和“错误”；');
        $this->writeText('7. 材料题请以【材料题开始】和【材料题结束】两个标签包裹起来，并且题干与子题之间，子题与子题之间都要用空行区分。');
        $this->writeText('8. 解析非必填，需要导入解析时，以中括号（注意是中文的中括号）标记开始，注意不要与题目之间有空行；');
        $this->writeText('9. 分值全部由系统默认生成，问答题：6分，其余题型：2分，可在导入成功后批量修改；');
        $this->writeText('10. 难度全部默认一般，可在导入成功后批量修改。');
    }

    protected function writeStartSignal()
    {
        $this->section->addTextBreak();
        $this->writeText('【导入开始】');
        $this->section->addTextBreak();
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
        $text = str_replace('&nbsp;', ' ', $text);
        $text = str_replace('&', '&amp;', $text);
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
