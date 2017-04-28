<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\Service\CourseService;

class CourseItem extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (!$course) {
            throw new ResourceNotFoundException('教学计划不存在');
        }

        return $this->convertToLeadingItems($this->getCourseService()->findCourseItems($courseId), $request->query->get('published', 0));
    }

    private function convertToLeadingItems($originItems, $onlyPublishTask = false)
    {
        $newItems = array();
        $number = 1;
        foreach ($originItems as $originItem) {
            $item = array();
            if ($originItem['itemType'] == 'task') {
                $item['type'] = 'task';
                $item['seq'] = '0';
                $item['number'] = strval($number++);
                $item['title'] = $originItem['title'];
                $item['task'] = $originItem;
                $newItems[] = $item;
                continue;
            }

            if ($originItem['itemType'] == 'chapter' && $originItem['type'] == 'lesson') {
                $taskSeq = count($originItem['tasks']) > 1 ? 1 : 0;
                foreach ($originItem['tasks'] as $task) {
                    $item['type'] = 'task';
                    $item['seq'] = strval($taskSeq);
                    $item['number'] = strval($number);
                    $item['title'] = $task['title'];
                    $item['task'] = $task;
                    $newItems[] = $item;
                    $taskSeq++;
                }
                $number++;
                continue;
            }

            $item['type'] = $originItem['type'];
            $item['seq'] = '0';
            $item['number'] = $originItem['number'];
            $item['title'] = $originItem['title'];
            $item['task'] =  null;
            $newItems[] = $item;
        }



        return $onlyPublishTask ? $this->filterUnPublishTask($newItems) : $newItems;
    }

    private function filterUnPublishTask($items)
    {
        foreach ($items as $key => $item) {
            if ($item['type'] == 'task' && $item['task']['status'] != 'published') {
                unset($items[$key]);
            }
        }

        return array_values($items);
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }
}