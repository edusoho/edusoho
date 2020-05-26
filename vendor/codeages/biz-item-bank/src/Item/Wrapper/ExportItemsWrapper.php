<?php

namespace Codeages\Biz\ItemBank\Item\Wrapper;

use Codeages\Biz\ItemBank\Item\Dao\QuestionDao;

class ExportItemsWrapper
{
    protected $biz;

    protected $imgRootDir;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function setImgRootDir($imgRootDir)
    {
        $this->imgRootDir = $imgRootDir;
    }

    public function wrap($items)
    {
        $num = 1;
        foreach ($items as &$item) {
            $item['num'] = $num++;
            $item = ('material' == $item['type']) ? $this->wrapMaterialItem($item) : $this->wrapNotMaterialItem($item);
            $item = $this->num($item);
            $item = $this->stem($item);
            $item = $this->options($item);
            $item = $this->answer($item);
            $item = $this->analysis($item);
            $item = $this->difficulty($item);
            $item = $this->subs($item);
            unset($item);
        }

        return $items;
    }

    protected function wrapMaterialItem($item)
    {
        $questions = $this->getQuestionDao()->findByItemId($item['id']);
        foreach ($questions as &$question) {
            $question['num'] = $question['seq'];
            $question['difficulty'] = $item['difficulty'];
            $question['type'] = $this->convertAnswerModeToType($question['answer_mode']);
            unset($question);
        }
        $item['stem'] = $item['material'];
        array_multisort(array_column($questions, 'num'), $questions);
        $item['subs'] = $questions;

        return $item;
    }

    protected function wrapNotMaterialItem($item)
    {
        $questions = $this->getQuestionDao()->findByItemId($item['id']);
        $question = array_shift($questions);
        $item['stem'] = $question['stem'];
        $item['response_points'] = $question['response_points'];
        $item['answer'] = $question['answer'];
        $item['analysis'] = $question['analysis'];
        $item['answer_mode'] = $question['answer_mode'];

        return $item;
    }

    protected function num($item)
    {
        $item['num'] = $item['num'].'、';

        return $item;
    }

    protected function stem($item)
    {
        if ('fill' == $item['type']) {
            $index = 0;
            $item['stem'] = preg_replace_callback('/\[\[\]\]/', function () use ($item, &$index) {
                return empty($item['answer'][$index]) ? '[[]]' : '[['.$item['answer'][$index++].']]';
            }, $item['stem']);
        }
        $item['stem'] = $this->stripTags($item['stem']);
        $item['stem'] = $this->explodeTextAndImg($item['stem']);

        return $item;
    }

    protected function options($item)
    {
        if (!in_array($item['type'], ['single_choice', 'uncertain_choice', 'choice'])) {
            return $item;
        }

        $answer = $this->biz['answer_mode_factory']->create($item['answer_mode']);
        $item['options'] = [];
        foreach ($item['response_points'] as $index => $responsePoint) {
            $option = $responsePoint[$answer::INPUT_TYPE]['text'];
            $option = $this->numberToCapitalLetter($index).'.'.$option;
            $option = $this->stripTags($option);
            $item['options'][$index] = $this->explodeTextAndImg($option);
        }

        return $item;
    }

    protected function answer($item)
    {
        if ('essay' == $item['type']) {
            $item['answer'] = $this->stripTags(implode($item['answer']));
            $item['answer'] = $this->explodeTextAndImg($item['answer']);
        } elseif ('determine' == $item['type']) {
            $item['answer'] = 'T' == implode($item['answer']) ? '正确' : '错误';
        } elseif (in_array($item['type'], array('choice', 'single_choice', 'uncertain_choice'))) {
            $item['answer'] = implode($item['answer']);
        }

        return $item;
    }

    protected function analysis($item)
    {
        if (!empty($item['analysis'])) {
            $item['analysis'] = $this->stripTags($item['analysis']);
            $item['analysis'] = $this->explodeTextAndImg($item['analysis']);
        }

        return $item;
    }

    protected function difficulty($item)
    {
        $difficulties = [
            'simple' => '简单',
            'normal' => '一般',
            'difficulty' => '困难',
        ];

        $item['difficulty'] = $difficulties[$item['difficulty']];

        return $item;
    }

    protected function subs($item)
    {
        if (empty($item['subs'])) {
            return $item;
        }

        foreach ($item['subs'] as $index => $sub) {
            $sub['isSub'] = 1;
            $sub['num'] = "（{$sub['num']}）";
            $sub = $this->stem($sub);
            $sub = $this->options($sub);
            $sub = $this->answer($sub);
            $sub = $this->analysis($sub);
            $sub = $this->difficulty($sub);
            $item['subs'][$index] = $sub;
        }

        return $item;
    }

    protected function explodeTextAndImg($text)
    {
        $elements = [];
        $result = preg_split('/(<img [^>]*?>)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($result as $content) {
            if (preg_match('/<img .*src=[\'\"](.*?)[\'\"].*>/', $content, $matches)) {
                if (empty($this->imgRootDir)) {
                    continue;
                }
                $imgSrc = $this->imgRootDir.$matches[1];
                if (!is_file($imgSrc) || false !== strpos($imgSrc, '.emf')) {
                    continue;
                }

                $elements[] = [
                    'element' => 'img',
                    'content' => $imgSrc,
                ];
            } else {
                $elements[] = [
                    'element' => 'text',
                    'content' => $content,
                ];
            }
        }

        return $elements;
    }

    protected function numberToCapitalLetter($number)
    {
        if (!is_int($number)) {
            $number = (int) $number;
        }

        return chr($number + 65);
    }

    protected function stripTags($str)
    {
        return trim(strip_tags($str, '<a><img>'));
    }

    protected function convertAnswerModeToType($answerMode)
    {
        $answerModeToType = [
            'single_choice' => 'single_choice',
            'uncertain_choice' => 'uncertain_choice',
            'choice' => 'choice',
            'true_false' => 'determine',
            'text' => 'fill',
            'rich_text' => 'essay',
        ];

        return $answerModeToType[$answerMode];
    }

    /**
     * @return QuestionDao
     */
    protected function getQuestionDao()
    {
        return $this->biz->dao('ItemBank:Item:QuestionDao');
    }
}
