<?php

namespace Topxia\Api\SpecialResponse;


class QiQiuYunV1OpenLessonResponse implements SpecialResponse
{
    public function filter($data)
    {
        if (isset($data['error'])) {
            return $data;
        }

        $resources = array();
        foreach ($data['resources'] as $openCourseLesson) {
            $resources[] = array(
                'id'          => $openCourseLesson['id'],
                'title'       => $openCourseLesson['title'],
                'summary'     => empty($openCourseLesson['summary']) ? '' : $openCourseLesson['summary'],
                'content'     => empty($openCourseLesson['content']) ? '' : $openCourseLesson['content'],
                'type'        => $openCourseLesson['type'],
                'mediaId'     => $openCourseLesson['mediaId'],
                'courseId'    => $openCourseLesson['courseId'],
                'chapterId'   => $openCourseLesson['chapterId'],
                'number'      => $openCourseLesson['number'],
                'free'        => '0',
                'learnedNum'  => '0',
                'viewedNum'   => '0',
                'createdTime' => $openCourseLesson['createdTime'],
                'updatedTime' => $openCourseLesson['updatedTime'],
            );

        }
        $data['resources'] = $resources;
        return $data;
    }
}