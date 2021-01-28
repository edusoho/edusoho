<?php

namespace ApiBundle\Api\Resource\Testpaper;

use ApiBundle\Api\Resource\Filter;

class TestpaperItemFilter extends Filter
{
    protected $publicFields = array('id', 'type', 'stem', 'score', 'answer', 'analysis', 'metas', 'categoryId', 'difficulty', 'target',
        'courseId', 'lessonId', 'parentId', 'subCount', 'finishedTimes', 'passedTimes', 'createdUserId', 'updatedUserId', 'courseSetId',
        'seq', 'missScore', 'missScore', 'subs', 'testResult', 'isDeleted', );

    protected function publicFields(&$data)
    {
        if (!empty($data['isDeleted'])) {
            unset($data['answer']);
        }

        if (!empty($data['stem'])) {
            $data['stem'] = $this->convertAbsoluteUrl($data['stem']);
        }

        if (!empty($data['analysis'])) {
            $data['analysis'] = $this->convertAbsoluteUrl($data['analysis']);
        }

        if (!empty($data['testResult'])) {
            if (!empty($data['testResult']['teacherSay'])) {
                $data['testResult']['teacherSay'] = $this->convertAbsoluteUrl($data['testResult']['teacherSay']);
            }
            if (!empty($data['testResult']['answer'])) {
                foreach ($data['testResult']['answer'] as &$answer) {
                    $answer = $this->convertAbsoluteUrl($answer);
                }
            }
        } else {
            $data['testResult'] = (object) array();
        }

        if (!empty($data['type'])) {
            if (in_array($data['type'], array('essay')) && !empty($data['answer']) && is_array($data['answer'])) {
                foreach ($data['answer'] as &$answer) {
                    $answer = $this->convertAbsoluteUrl($answer);
                }
            }

            if (in_array($data['type'], array('fill')) && !empty($data['answer']) && is_array($data['answer'])) {
                foreach ($data['answer'] as &$answer) {
                    if (is_array($answer)) {
                        $answer = implode('|', $answer);
                    }
                }
            }

            if (in_array($data['type'], array('single_choice', 'choice', 'uncertain_choice'))
                && !empty($data['metas'])
                && !empty($data['metas']['choices'])
                && is_array($data['metas']['choices'])) {
                foreach ($data['metas']['choices'] as &$choice) {
                    $choice = $this->convertAbsoluteUrl($choice);
                }
            }

            if (in_array($data['type'], array('material'))) {
                $data['subs'] = array_values($data['subs']);
                foreach ($data['subs'] as &$question) {
                    self::filter($question);
                }
            }
        }
    }
}
