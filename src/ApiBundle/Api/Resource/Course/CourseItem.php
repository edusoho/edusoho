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
    public function search(ApiRequest $apiRequest, $courseId)
    {
        $course = $this->service('Course:CourseService')->getCourse($courseId);

        if (!$course) {
            throw new ResourceNotFoundException('教学计划不存在');
        }

        return $this->convertToLeadingItems($this->getCourseService()->findCourseItems($courseId));
    }

    private function convertToLeadingItems($originItems)
    {
        $newItems = array();
        foreach ($originItems as $originItem) {
            $item = array();
            if ($originItem['itemType'] == 'task') {
                $item['type'] = 'task';
                $item['seq'] = 0;
                $item['number'] = $originItem['number'];
                $item['title'] = $originItem['title'];
                $item['task'] = $originItem;
                $newItems[] = $item;
                continue;
            }

            if ($originItem['itemType'] == 'chapter' && $originItem['type'] == 'lesson') {
                $taskSeq = count($originItem['tasks']) > 1 ? 1 : 0;
                foreach ($originItem['tasks'] as $task) {
                    $item['type'] = 'task';
                    $item['seq'] = $taskSeq;
                    $item['number'] = $task['number'];
                    $item['title'] = $task['title'];
                    $item['task'] = $task;
                    $newItems[] = $item;
                    $taskSeq++;
                }
                continue;
            }

            $item['type'] = $originItem['type'];
            $item['seq'] = 0;
            $item['number'] = $originItem['number'];
            $item['title'] = $originItem['title'];
            $item['task'] =  null;
            $newItems[] = $item;
        }

        return $newItems;
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }
}