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
                'id'           => $openCourseLesson['id'],
                'title'        => $openCourseLesson['title'],
                'summary'      => $openCourseLesson['summary'],
                'content'      => $openCourseLesson['content'],
                'type'         => $openCourseLesson['type'],
                'mediaId'      => $openCourseLesson['mediaId'],
                'openCourseId' => $openCourseLesson['courseId'],
                'chapterId'    => $openCourseLesson['chapterId'],
                'number'       => $openCourseLesson['number'],
                'free'         => $openCourseLesson['free'],
                'learnedNum'   => $openCourseLesson['learnedNum'],
                'viewedNum'    => $openCourseLesson['viewedNum'],
                'createdTime'  => $openCourseLesson['createdTime'],
                'updatedTime'  => $openCourseLesson['updatedTime'],
            );

        }
        $data['resources'] = $resources;
        return $data;
    }
}