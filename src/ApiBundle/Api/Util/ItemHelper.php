<?php

namespace ApiBundle\Api\Util;

use AppBundle\Common\ArrayToolkit;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ItemHelper
{
    private $biz;

    private $container;

    private $blankChapter = ['type' => 'chapter', 'isExist' => 0];

    private $blankUnit = ['type' => 'unit', 'isExist' => 0];

    public function __construct($biz, ContainerInterface $container)
    {
        $this->biz = $biz;
        $this->container = $container;
    }

    /**
     * 章->节->课时 结构
     * 向上补全结构，向下补全至节
     */
    public function convertToTree($items)
    {
        $treeItems = [];

        if (empty($items)) {
            return $treeItems;
        }

        $nowChapterIndex = $nowUnitIndex = -1;

        // 如果第一章上方还有内容，则归入未分类章
        if ('chapter' != $items[0]['type']) {
            $treeItems[] = $this->blankChapter;
            $lastItem = 'chapter';
            ++$nowChapterIndex;
        } else {
            $lastItem = 'default';
        }

        foreach ($items as $index => $item) {
            $item['isExist'] = 1;

            switch ($item['type']) {
                case 'chapter':
                    // 如果上一章没有节或课时，则补全节
                    if ('chapter' == $lastItem) {
                        ++$nowUnitIndex;
                        $treeItems[$nowChapterIndex]['children'][] = $this->blankUnit;
                        $treeItems[$nowChapterIndex]['children'][$nowUnitIndex]['children'] = [];
                    }
                    ++$nowChapterIndex;
                    $nowUnitIndex = -1; //新章创建后，应重置当前节
                    $treeItems[$nowChapterIndex] = $item;
                    $treeItems[$nowChapterIndex]['children'] = [];
                    break;

                case 'unit':
                    ++$nowUnitIndex;
                    $treeItems[$nowChapterIndex]['children'][] = $item;
                    $treeItems[$nowChapterIndex]['children'][$nowUnitIndex]['children'] = [];
                    break;

                case 'lesson':
                    // 如果章下面直接是课时，则补全节
                    if ('chapter' == $lastItem) {
                        ++$nowUnitIndex;
                        $treeItems[$nowChapterIndex]['children'][] = $this->blankUnit;
                    }
                    // 在对应节下面加入课程
                    $treeItems[$nowChapterIndex]['children'][$nowUnitIndex]['children'][] = $item;
                    break;

                default:
                    break;
            }

            $lastItem = $item['type'];
        }

        // 以章结尾，补全节
        if ('chapter' == $lastItem) {
            ++$nowUnitIndex;
            $treeItems[$nowChapterIndex]['children'][] = $this->blankUnit;
            $treeItems[$nowChapterIndex]['children'][$nowUnitIndex]['children'] = [];
        }

        return $treeItems;
    }

    public function convertToTreeV2($items)
    {
        $treeItems = [];
        if (empty($items)) {
            return $treeItems;
        }

        $nowChapterIndex = $nowUnitIndex = -1;
        $depth = 1;

        $lastItem = $items[0]['type'];
        $bottomItem = $items[0]['type'];

        foreach ($items as $index => $item) {
            switch ($item['type']) {
                case 'chapter':
                    $depth = 1;
                    break;
                case 'unit':
                    if ('chapter' == $lastItem) {
                        ++$depth;
                    }

                    if ('lesson' == $lastItem && 2 == $depth && 'unit' == $bottomItem) {
                        --$depth;
                    }

                    if ('lesson' == $lastItem && 3 == $depth) {
                        --$depth;
                    }
                    break;
                case 'lesson':
                    if (in_array($lastItem, ['chapter', 'unit'])) {
                        ++$depth;
                    }
                    break;
                default:
                    break;
            }

            if (1 == $depth) {
                ++$nowChapterIndex;
                $bottomItem = $item['type'];
                $nowUnitIndex = -1;
                $treeItems[] = $item;
                'lesson' == $item['type'] ? null : $treeItems[$nowChapterIndex]['children'] = [];
            }

            if (2 == $depth) {
                ++$nowUnitIndex;
                $treeItems[$nowChapterIndex]['children'][] = $item;
                'lesson' == $item['type'] ? null : $treeItems[$nowChapterIndex]['children'][$nowUnitIndex]['children'] = [];
            }

            if (3 == $depth) {
                $treeItems[$nowChapterIndex]['children'][$nowUnitIndex]['children'][] = $item;
            }

            $lastItem = $item['type'];
        }

        return $treeItems;
    }

    public function convertToLeadingItemsV1($originItems, $course, $isSsl, $fetchSubtitlesUrls, $onlyPublishTask = false)
    {
        $courseId = $course['id'];
        $newItems = [];
        $number = 1;
        $targetUrl = $this->generateUrl('my_course_show', ['id' => $courseId]);
        foreach ($originItems as $originItem) {
            $item = [];
            if ('task' == $originItem['itemType']) {
                $originItem['courseUrl'] = $targetUrl;
                $item['id'] = $originItem['id'];
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
                $originItem['tasks'] = empty($originItem['tasks']) ? [] : $originItem['tasks'];
                $taskSeq = count($originItem['tasks']) > 1 ? 1 : 0;
                foreach ($originItem['tasks'] as $task) {
                    $task['courseUrl'] = $targetUrl;
                    $item['type'] = 'task';
                    $item['seq'] = strval($taskSeq);
                    $item['number'] = strval($number);
                    $item['id'] = $originItem['id'];
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
            $item['id'] = $originItem['id'];
            $item['task'] = null;
            $newItems[] = $item;
        }

        $result = $onlyPublishTask ? $this->filterUnPublishTaskV1($newItems) : $this->isHiddenUnpublishTasksV1($newItems, $courseId);

        return $fetchSubtitlesUrls ? $this->afterDeal($result, $isSsl) : $result;
    }

    public function convertToLeadingItemsV2($originItems, $course, $isSsl, $fetchSubtitlesUrls, $onlyPublishTask = false, $showOptionalNum = 1)
    {
        $result = [];
        $lessonInfos = [];
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

        $result = [];
        $lessonNum = 1;
        foreach ($convertedItems as $key => $item) {
            if ('task' == $item['type']) {
                $lessonId = $item['task']['categoryId'];
                if (!empty($lessonInfos[$lessonId])) {
                    $lessonItem = $lessonInfos[$lessonId];
                    $lessonItem['number'] = 1 == $showOptionalNum ? $lessonNum : $lessonItem['number'];
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

    protected function generateUrl($route, $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_URL)
    {
        return $this->container->get('router')->generate($route, $parameters, $referenceType);
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
