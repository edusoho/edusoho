<?php

namespace ApiBundle\Api\Resource\Testpaper;

use ApiBundle\Api\Resource\Course\CourseTaskFilter;
use ApiBundle\Api\Resource\Filter;

class TestpaperActionFilter extends Filter
{
    protected $publicFields = array('testpaperResult', 'testpaper', 'items', 'task');

    protected function publicFields(&$data)
    {
        if (!empty($data['items'])) {
            $isShowTestResult = empty($data['isShowTestResult']) ? 0 : 1;
            $data['items'] = $this->coverTestpaperItems($data['items'], $isShowTestResult);
        }

        if (!empty($data['task'])) {
            $tasKFilter = new CourseTaskFilter();
            $tasKFilter->filter($data['task']);
        }
    }

    private function coverTestpaperItems($items, $isShowTestResult)
    {
        $self = $this;
        $result = array_map(function ($item) use ($self, $isShowTestResult) {
            $item = array_map(function ($itemValue) use ($self, $isShowTestResult) {
                $question = $itemValue['question'];

                if (isset($question['isDeleted']) && true == $question['isDeleted']) {
                    return array();
                }
                if (isset($itemValue['items'])) {
                    $filterItems = array_values($itemValue['items']);
                    $itemValue['items'] = array_map(function ($filterItem) use ($self, $isShowTestResult) {
                        return $self->filterMetas($filterItem, $isShowTestResult);
                    }, $filterItems);
                }

                $itemValue = $self->filterMetas($itemValue, $isShowTestResult);

                return $itemValue;
            }, $item);
            if ($self->isArrayEmpty($item)) {
                return;
            }

            return array_values($item);
        }, $items);

        foreach ($result as $key => $value) {
            if (empty($value)) {
                $result[$key] = array();
            }

            foreach ($result[$key] as $k => $v) {
                if (empty($v)) {
                    unset($result[$key][$k]);
                }
            }

            $result[$key] = array_values($result[$key]);

            uasort(
                $result[$key],
                function ($item1, $item2) {
                    return $item1['seq'] > $item2['seq'];
                }
            );
        }

        return $result;
    }

    public function filterMetas($itemValue, $isShowTestResult)
    {
        $question = $itemValue['question'];
        $question['stem'] = $this->convertAbsoluteUrl($question['stem']);
        $question['analysis'] = $this->convertAbsoluteUrl($question['analysis']);

        if (!$isShowTestResult && isset($question['testResult'])) {
            unset($question['testResult']);
        }

        if (isset($question['testResult'])) {
            if (!empty($question['testResult']['answer'][0])) {
                $question['testResult']['answer'][0] = $this->convertAbsoluteUrl($question['testResult']['answer'][0]);
            }

            if (!empty($question['testResult']['teacherSay'])) {
                $question['testResult']['teacherSay'] = $this->convertAbsoluteUrl($question['testResult']['teacherSay']);
            }
        }

        $itemValue['question'] = $question;
        $self = $this;
        if (isset($question['metas'])) {
            $metas = $question['metas'];
            if (isset($metas['choices'])) {
                $metas = array_values($metas['choices']);

                $itemValue['question']['metas'] = array_map(function ($choice) use ($self) {
                    return $self->convertAbsoluteUrl($choice);
                }, $metas);
            }
        }

        $answer = $question['answer'];
        if (is_array($answer)) {
            $itemValue['question']['answer'] = array_map(function ($answerValue) use ($self) {
                if (is_array($answerValue)) {
                    return implode('|', $answerValue);
                }

                return $self->convertAbsoluteUrl($answerValue);
            }, $answer);

            return $itemValue;
        }

        return $itemValue;
    }

    /**
     * @param $array
     *
     * @return bool
     *              判断子数组和父级数组是否都为空
     */
    protected function isArrayEmpty($array)
    {
        foreach ($array as $key => $value) {
            if (!empty($value)) {
                return false;
            }
        }

        return true;
    }
}
