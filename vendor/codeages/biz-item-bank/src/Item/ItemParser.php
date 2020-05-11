<?php

namespace Codeages\Biz\ItemBank\Item;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\ItemBank\ErrorCode;
use Codeages\Biz\ItemBank\Item\AnswerMode\ChoiceAnswerMode;
use Codeages\Biz\ItemBank\Item\AnswerMode\RichTextAnswerMode;
use Codeages\Biz\ItemBank\Item\AnswerMode\SingleChoiceAnswerMode;
use Codeages\Biz\ItemBank\Item\AnswerMode\TextAnswerMode;
use Codeages\Biz\ItemBank\Item\AnswerMode\TrueFalseAnswerMode;
use Codeages\Biz\ItemBank\Item\AnswerMode\UncertainChoiceAnswerMode;
use Codeages\Biz\ItemBank\Item\Exception\ItemException;
use ExamParser\Parser\Parser;
use ExamParser\Reader\ReadDocx;

class ItemParser
{
    protected $biz;

    protected $filePath = '';

    protected $resourceTmpPath = '/tmp';

    protected $answerMode = [
        'single_choice' => SingleChoiceAnswerMode::NAME,
        'choice' => ChoiceAnswerMode::NAME,
        'uncertain_choice' => UncertainChoiceAnswerMode::NAME,
        'determine' => TrueFalseAnswerMode::NAME,
        'fill' => TextAnswerMode::NAME,
        'essay' => RichTextAnswerMode::NAME,
    ];

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function setConfig($filePath, $options = [])
    {
        $this->filePath = $filePath;
        if (isset($options['resourceTmpPath'])) {
            $this->resourceTmpPath = $options['resourceTmpPath'];
        }
    }

    public function read($filePath, $options = [])
    {
        $this->setConfig($filePath, $options);

        $wordRead = new ReadDocx($this->filePath, ['resourceTmpPath' => $this->resourceTmpPath]);
        
        return $wordRead->read();
    }

    public function parse($text)
    {
        $parser = new Parser($text);
        $data = $parser->parser();

        return $this->formatData($data);
    }

    public function formatData($data)
    {
        $formatData = [];

        foreach ($data as $question) {
            $item = [
                'category_id' => empty($question['category_id']) ? '0' : $question['category_id'],
                'type' => $question['type'],
                'material' => ('material' == $question['type']) ? $question['stem'] : '',
                'analysis' => ('material' == $question['type']) ? empty($question['analysis']) ? '' : $question['analysis'] : '',
                'difficulty' => $question['difficulty'],
                'score' => ('material' == $question['type']) ? 0 : (float) $question['score'],
                'attachments' => [],
            ];

            $itemQuestions = [];
            if (!empty($question['subQuestions'])) {
                $item = $this->checkErrors($item, $question);
                foreach ($question['subQuestions'] as $subQuestion) {
                    $itemQuestions[] = $this->convertItemQuestion($subQuestion);
                }
            } else {
                $itemQuestions[] = $this->convertItemQuestion($question);
            }

            $item['questions'] = $itemQuestions;
            $formatData[] = $item;
        }

        return $formatData;
    }

    protected function convertItemQuestion($question)
    {
        $itemQuestion = [
            'stem' => $question['stem'],
            'score' => (float) $question['score'],
            'options' => empty($question['options']) ? '' : $question['options'],
            'analysis' => empty($question['analysis']) ? '' : $question['analysis'],
            'stemShow' => empty($question['stemShow']) ? '' : $question['stemShow'],
            'attachments' => [],
        ];

        $itemQuestion = $this->getAnswer($itemQuestion, $question);

        return $this->checkErrors($itemQuestion, $question);
    }

    protected function getAnswer($itemQuestion, $question)
    {
        $itemQuestion['answer_mode'] = $this->getAnswerMode($question['type']);

        $itemQuestion = $this->getAnswerModeClass($itemQuestion['answer_mode'])->parse($itemQuestion, $question);

        return $itemQuestion;
    }

    protected function checkErrors($itemQuestion, $question)
    {
        if (!empty($question['errors'])) {
            $itemQuestion['errors'] = $question['errors'];
        }

        return $itemQuestion;
    }

    protected function getAnswerMode($type)
    {
        if (empty($this->answerMode[$type])) {
            throw new ItemException('mode not exist', ErrorCode::ANSWER_MODE_NOTFOUND);
        }

        return $this->answerMode[$type];
    }

    protected function getAnswerModeClass($answerMode)
    {
        return $this->biz['answer_mode_factory']->create($answerMode);
    }
}
