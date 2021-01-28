<?php

namespace AppBundle\Component\Wrapper;

use Topxia\Service\Common\ServiceKernel;

class ExportQuestionWrapper extends Wrapper
{
    public function seq($question)
    {
        $question['seq'] = $question['seq'].'、';

        return $question;
    }

    public function num($question)
    {
        if (empty($question['num'])) {
            return $question;
        }

        $question['num'] = $question['num'].'、';

        return $question;
    }

    public function stem($question)
    {
        $question['stem'] = $this->stripTags($question['stem']);
        $question['stem'] = $this->explodeTextAndImg($question['stem']);
        $question['stem'] = $this->filterImage($question['stem']);

        return $question;
    }

    public function options($question)
    {
        $question['options'] = empty($question['metas']['choices']) ? array() : $question['metas']['choices'];
        foreach ($question['options'] as $index => $option) {
            $option = $this->numberToCapitalLetter($index).'.'.$option;
            $option = $this->stripTags($option);
            $question['options'][$index] = $this->explodeTextAndImg($option);
        }

        return $question;
    }

    public function answer($question)
    {
        if ('essay' == $question['type']) {
            $question['answer'] = $this->stripTags(implode($question['answer']));
            $question['answer'] = $this->explodeTextAndImg($question['answer']);
        } elseif ('determine' == $question['type']) {
            $determineAnswer = array(
                '错误',
                '正确',
            );
            $answer = (int) reset($question['answer']);
            $question['answer'] = $determineAnswer[$answer];
        } elseif (in_array($question['type'], array('choice', 'single_choice', 'uncertain_choice'))) {
            $choiceAnswer = '';
            foreach ($question['answer'] as $answer) {
                $choiceAnswer .= $this->numberToCapitalLetter($answer);
            }
            $question['answer'] = $choiceAnswer;
        }

        return $question;
    }

    public function difficulty($question)
    {
        $difficulties = array(
            'simple' => '简单',
            'normal' => '一般',
            'difficulty' => '困难',
        );

        $question['difficulty'] = $difficulties[$question['difficulty']];

        return $question;
    }

    public function analysis($question)
    {
        if (!empty($question['analysis'])) {
            $question['analysis'] = $this->stripTags($question['analysis']);
            $question['analysis'] = $this->explodeTextAndImg($question['analysis']);
        }

        return $question;
    }

    public function subs($question)
    {
        if (empty($question['subs'])) {
            return $question;
        }

        foreach ($question['subs'] as $index => $sub) {
            $sub['isSub'] = 1;
            $sub['seq'] = "（{$sub['seq']}）";
            $sub['num'] = $sub['seq'];
            $sub = $this->stem($sub);
            $sub = $this->options($sub);
            $sub = $this->answer($sub);
            $sub = $this->difficulty($sub);
            $sub = $this->analysis($sub);
            $question['subs'][$index] = $sub;
        }

        return $question;
    }

    protected function explodeTextAndImg($text)
    {
        $items = array();
        $webDir = ServiceKernel::instance()->getParameter('kernel.root_dir').'/../web';
        $result = preg_split('/(<img [^>]*?>)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($result as $item) {
            if (preg_match('/<img .*src=[\'\"](.*?)[\'\"].*>/', $item, $matches) && is_file($webDir.$matches[1])) {
                if (strpos($webDir.$matches[1], '.emf') !== false) {
                    continue;
                }

                $items[] = array(
                    'element' => 'img',
                    'content' => $webDir.$matches[1],
                );
            } else {
                $items[] = array(
                    'element' => 'text',
                    'content' => $item,
                );
            }
        }

        return $items;
    }

    protected function filterImage($stems)
    {
        $items = array();
        foreach ($stems as $stem) {
            if ('img' == $stem['element'] && !file_exists($stem['content'])) {
                continue;
            }

            $items[] = $stem;
        }

        return $items;
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

    protected function getWrapList()
    {
        return array(
            'seq',
            'stem',
            'options',
            'answer',
            'difficulty',
            'analysis',
            'subs',
            'num',
        );
    }
}
