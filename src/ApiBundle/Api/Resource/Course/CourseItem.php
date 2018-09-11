<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\Service\CourseService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CourseItem extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (!$course) {
            throw new NotFoundHttpException('教学计划不存在', null, ErrorCode::RESOURCE_NOT_FOUND);
        }

        return $this->convertToLeadingItems($this->getCourseService()->findCourseItems($courseId), $course, $request->query->get('onlyPublished', 0));
    }

    protected function convertToLeadingItems($originItems, $course, $onlyPublishTask = false)
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

        $result = $onlyPublishTask ? $this->filterUnPublishTask($newItems) : $this->isHiddenUnpublishTasks($newItems, $courseId);

        return $this->afterDeal($result);
    }

    protected function filterUnPublishTask($items)
    {
        foreach ($items as $key => $item) {
            if ('task' == $item['type'] && 'published' != $item['task']['status']) {
                unset($items[$key]);
            }
        }

        return array_values($items);
    }

    protected function isHiddenUnpublishTasks($items, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if ($course['isHideUnpublish']) {
            return $this->filterUnPublishTask($items);
        }

        return $items;
    }

    protected function afterDeal($result)
    {
        foreach ($result as $key => $taskItem) {
            if (!empty($taskItem['task']) && !empty($taskItem['task']['activity'])) {
                $updatedTaskInfo = $this->getSubtitleService()->setSubtitlesUrls(
                    $taskItem['task']['activity']['ext'],
                    false
                );

                if (!empty($updatedTaskInfo['subtitlesUrls'])) {
                    $result[$key]['task']['subtitlesUrls'] = $updatedTaskInfo['subtitlesUrls'];
                }
            }
        }

        return $result;
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    private function getSubtitleService()
    {
        return $this->service('Subtitle:SubtitleService');
    }
}
