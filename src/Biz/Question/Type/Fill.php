<?php

namespace Biz\Question\Type;

use Topxia\Common\ArrayToolkit;
use Topxia\Common\Exception\UnexpectedValueException;

class Fill implements TypeInterface
{
    public function create($fields)
    {
    }

    public function update($id, $fields)
    {
    }

    public function delete($id)
    {
    }

    public function get($id)
    {
    }

    public function filter($fields)
    {
        $fields = $this->commonFilter($fields);

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

    protected function commonFilter($fields)
    {
        if (!empty($fields['target']) && $fields['target'] > 0) {
            $fields['lessonId'] = $fields['target'];
            unset($fields['target']);
        }
        $fields = ArrayToolkit::parts($fields, array(
            'type',
            'stem',
            'difficulty',
            'userId',
            'answer',
            'analysis',
            'metas',
            'score',
            'categoryId',
            'parentId',
            'copyId',
            'target',
            'courseId',
            'lessonId',
            'subCount',
            'finishedTimes',
            'passedTimes',
            'userId',
            'updatedTime',
            'createdTime'
        ));

        return $fields;
    }
}
