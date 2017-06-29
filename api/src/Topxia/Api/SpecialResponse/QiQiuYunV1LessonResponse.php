<?php

namespace Topxia\Api\SpecialResponse;


class QiQiuYunV1LessonResponse implements SpecialResponse
{
    public function filter($data)
    {
        if (isset($data['error'])) {
            return $data;
        }
        
        if (!isset($data['resources'])) {
            return $data;
        }

        $resources = array();
        foreach ($data['resources'] as $lesson) {
            $resources[] = array(
                'id' => $lesson['id'],
                'title' => $lesson['title'],
                'summary' => $lesson['summary'],
                'content' => isset($lesson['content']) ? $lesson['content']:'',
                'type' => $lesson['type'],
                'mediaId' => isset($lesson['mediaId']) ? $lesson['mediaId']:'',
                'courseId' => $lesson['courseId'],
                'chapterId' => $lesson['chapterId'],
                'number' => $lesson['number'],
                'free' => $lesson['free'],
                'learnedNum' => isset($lesson['learnedNum']) ? $lesson['learnedNum']:'',
                'viewedNum' => isset($lesson['viewedNum']) ? $lesson['viewedNum']:'',
                'createdTime' => $lesson['createdTime'],
                'updatedTime' => $lesson['updatedTime'],
            );
        }
        $data['resources'] = $resources;

        return $data;
    }
}