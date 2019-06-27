<?php

namespace ApiBundle\Api\Util;

use AppBundle\Common\ArrayToolkit;

class ItemHelper
{
    private $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function convertToTree($items)
    {
        $firstChapterIndex = $this->getFirstChapterIndex($items);
        $result = $this->getTreeItems($items, $firstChapterIndex);

        return $result;
    }

    /**
     * 严格的 章->节->课时 结构，如果缺少层级，则补上空结构(利于 android，ios 数据组装)
     */
    private function getTreeItems($items, $firstChapterIndex)
    {
        $result = array();

        $nowChapterIndex = -1;
        $nowUnitIndex = -1;
        $lastItem = 'default';

        // 如果第一章上方还有内容，则归入未分类章
        if (0 != $firstChapterIndex) {
            $result[] = $this->getBlankChapterOrUnit('chapter');
            $lastItem = 'chapter';
            ++$nowChapterIndex;
        }

        foreach ($items as $index => $item) {
            $item['isExist'] = 1;

            if ('chapter' == $item['type']) {
                // 创建新章前，保证之前结构完整性
                if ('unit' == $lastItem) {
                    $result[$nowChapterIndex]['children'][$nowUnitIndex]['children'][] = $this->getBlankLesson();
                }

                if ('chapter' == $lastItem) {
                    $result[$nowChapterIndex]['children'][] = $this->getBlankChapterOrUnit('unit');
                    ++$nowUnitIndex;
                    $result[$nowChapterIndex]['children'][$nowUnitIndex]['children'][] = $this->getBlankLesson();
                }

                ++$nowChapterIndex;
                $nowUnitIndex = -1; //新章创建后，应重置当前节
                $result[$nowChapterIndex] = $item;
                $lastItem = 'chapter';
            } elseif ('unit' == $item['type']) {
                ++$nowUnitIndex;
                $result[$nowChapterIndex]['children'][] = $item;
                $lastItem = 'unit';
            } elseif ('lesson' == $item['type']) {
                // 如果章下面直接是课时，则补全节
                if ('chapter' == $lastItem) {
                    ++$nowUnitIndex;
                    $result[$nowChapterIndex]['children'][] = $this->getBlankChapterOrUnit('unit');
                }

                $result[$nowChapterIndex]['children'][$nowUnitIndex]['children'][] = $item;
                $lastItem = 'lesson';
            }
        }

        // 如果以章/节结束，则补齐剩余结构
        if ('lesson' != $lastItem) {
            if ('chapter' == $lastItem) {
                $result[$nowChapterIndex]['children'][] = $this->getBlankChapterOrUnit('unit');
                ++$nowUnitIndex;
            }
            $result[$nowChapterIndex]['children'][$nowUnitIndex]['children'][] = $this->getBlankLesson();
        }

        return $result;
    }

    private function getBlankLesson()
    {
        return array(
            'id' => '',
            'courseId' => '',
            'type' => '',
            'number' => 0,
            'seq' => '',
            'title' => '',
            'createdTime' => '',
            'updatedTime' => '',
            'copyId' => '',
            'status' => '',
            'isOptional' => '',
            'published_number' => '',
            'itemType' => '',
            'isExist' => 0,
        );
    }

    private function getBlankChapterOrUnit($type)
    {
        return array(
            'type' => $type,
            'isExist' => 0,
            'seq' => '',
            'number' => '',
            'title' => '',
            'task' => null,
        );
    }

    private function getFirstChapterIndex($items)
    {
        $firstChapterIndex = 0;

        foreach ($items as $index => $item) {
            if ('chapter' == $item['type']) {
                $firstChapterIndex = $index;
                break;
            }
        }

        return $firstChapterIndex;
    }

    public function convertToLeadingItemsV1($originItems, $course, $isSsl, $fetchSubtitlesUrls, $onlyPublishTask = false)
    {
        $courseId = $course['id'];
        $newItems = array();
        $number = 1;
        foreach ($originItems as $originItem) {
            $item = array();
            if ('task' == $originItem['itemType']) {
                $item['type'] = 'task';
                $item['seq'] = '0';
                $item['number'] = strval($number++);
                $item['title'] = $originItem['title'];
                $item['task'] = $originItem;
                $newItems[] = $item;
                continue;
            }

            if ('chapter' == $originItem['itemType'] && 'lesson' == $originItem['type']) {
                if ('normal' == $course['courseType']) {
                    continue;
                }
                $originItem['tasks'] = empty($originItem['tasks']) ? array() : $originItem['tasks'];
                $taskSeq = count($originItem['tasks']) > 1 ? 1 : 0;
                foreach ($originItem['tasks'] as $task) {
                    $item['type'] = 'task';
                    $item['seq'] = strval($taskSeq);
                    $item['number'] = strval($number);
                    $item['title'] = $task['title'];
                    $item['task'] = $task;
                    $newItems[] = $item;
                    ++$taskSeq;
                }
                ++$number;
                continue;
            }

            $item['type'] = $originItem['type'];
            $item['seq'] = '0';
            $item['number'] = $originItem['number'];
            $item['title'] = $originItem['title'];
            $item['task'] = null;
            $newItems[] = $item;
        }

        $result = $onlyPublishTask ? $this->filterUnPublishTaskV1($newItems) : $this->isHiddenUnpublishTasksV1($newItems, $courseId);

        return $fetchSubtitlesUrls ? $this->afterDeal($result, $isSsl) : $result;
    }

    public function convertToLeadingItemsV2($originItems, $course, $isSsl, $fetchSubtitlesUrls, $onlyPublishTask = false)
    {
        $result = array();
        $lessonInfos = array();
        foreach ($originItems as $item) {
            if ('lesson' == $item['type']) {
                unset($item['tasks']);
                $lessonInfos[$item['id']] = $item;
            }
        }

        $convertedItems = $this->convertToLeadingItemsV1($originItems, $course, $isSsl, $fetchSubtitlesUrls, $onlyPublishTask);
        foreach ($convertedItems as $key => $item) {
            if ('task' == $item['type']) {
                $lessonId = $item['task']['categoryId'];
                unset($item['task']['activity']['content']);
                $lessonInfos[$lessonId]['tasks'][] = $item['task'];
            }
        }
        $lessonInfos = $onlyPublishTask ? $this->filterUnPublishLessonV2($lessonInfos) : $this->isHiddenUnpublishTasksV2($lessonInfos, $course['id']);
        $lessonInfos = ArrayToolkit::index($lessonInfos, 'id');

        $result = array();
        $lessonNum = 1;
        foreach ($convertedItems as $key => $item) {
            if ('task' == $item['type']) {
                $lessonId = $item['task']['categoryId'];
                if (!empty($lessonInfos[$lessonId])) {
                    $lessonItem = $lessonInfos[$lessonId];
                    $lessonItem['number'] = $lessonNum;
                    $result[] = $lessonItem;
                    ++$lessonNum;
                    unset($lessonInfos[$item['task']['categoryId']]);
                }
            } else {
                $result[] = $item;
            }
        }

        return $result;
    }

    protected function filterUnPublishLessonV2($lessonInfos)
    {
        foreach ($lessonInfos as $itemKey => $item) {
            if ('published' != $item['status']) {
                unset($lessonInfos[$itemKey]);
            }
        }

        return $lessonInfos;
    }

    protected function isHiddenUnpublishTasksV2($lessonInfos, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if ($course['isHideUnpublish']) {
            return $this->filterUnPublishLessonV2($lessonInfos);
        }

        return $lessonInfos;
    }

    protected function filterUnPublishTaskV1($items)
    {
        foreach ($items as $key => $item) {
            if ('task' == $item['type'] && 'published' != $item['task']['status']) {
                unset($items[$key]);
            }
        }

        return array_values($items);
    }

    protected function isHiddenUnpublishTasksV1($items, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if ($course['isHideUnpublish']) {
            return $this->filterUnPublishTaskV1($items);
        }

        return $items;
    }

    protected function afterDeal($result, $isSsl)
    {
        foreach ($result as $key => $taskItem) {
            if (!empty($taskItem['task']) && !empty($taskItem['task']['activity'])) {
                $updatedTaskInfo = $this->getSubtitleService()->setSubtitlesUrls(
                    $taskItem['task']['activity']['ext'],
                    $isSsl
                );

                if (!empty($updatedTaskInfo['subtitlesUrls'])) {
                    $result[$key]['task']['subtitlesUrls'] = $updatedTaskInfo['subtitlesUrls'];
                }
            }
        }

        return $result;
    }

    protected function getSubtitleService()
    {
        return $this->biz->service('Subtitle:SubtitleService');
    }

    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }
}
