<?php

namespace Biz\FillQuestion;

use Biz\Question\Config\Question;
use Topxia\Common\Exception\UnexpectedValueException;

class FillQuestion extends Question
{
    public function getMetas()
    {
        return array(
            'name' => '填空题'
        );
    }

    public function registerActions()
    {
        return array(
            'create' => 'WebBundle:FillQuestion:create',
            'edit'   => 'WebBundle:FillQuestion:edit',
            'show'   => 'WebBundle:FillQuestion:show'
        );
    }

    public function registerTemplates()
    {
        return array(
            'create' => 'WebBundle:FillQuestion:form.html.twig',
            'edit'   => 'WebBundle:FillQuestion:form.html.twig',
            'do'     => 'WebBundle:FillQuestion:do.html.twig'
        );
    }

    protected function registerListeners()
    {
        // TODO: Implement registerListeners() method.
    }

    public function create($fields)
    {
    }

    public function update($targetId, $fields)
    {
    }

    public function delete($targetId)
    {
    }

    public function get($targetId)
    {
    }

    public function filter($fields, $mode)
    {
        $fields = parent::commonFilter($fields, $mode);

        preg_match_all("/\[\[(.+?)\]\]/", $fields['stem'], $answer, PREG_PATTERN_ORDER);
        if (empty($answer[1])) {
            throw new UnexpectedValueException("This Question Answer Unexpected");
        }

        $fields['answer'] = array();
        foreach ($answer[1] as $value) {
            $value = explode('|', $value);
            foreach ($value as $i => $v) {
                $value[$i] = trim($v);
            }
            $fields['answer'][] = $value;
        }

        return $fields;
    }
}
