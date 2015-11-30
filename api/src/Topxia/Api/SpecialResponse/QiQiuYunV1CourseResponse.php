<?php

namespace Topxia\Api\SpecialResponse;


class QiQiuYunV1CourseResponse implements SpecialResponse
{
    public function filter($data)
    {
        $resources = array();
        foreach ($data['resources'] as $course) {
            $resources[] = array(
                'id' => $course['id'],
                'title' => $course['title'],
                'subtitle' => $course['subtitle'],
                'type' => $course['type'],
                'price' => $course['price'],
                'lessonNum' => $course['lessonNum'],
                'ratingNum' => $course['ratingNum'],
                'tags' => $course['tags'],
                'about' => $course['about'],
                'goals' => $course['goals'],
                'smallPicture' => $course['smallPicture'],
                'middlePicture' => $course['middlePicture'],
                'largePicture' => $course['largePicture'],
                'audiences' => $course['audiences'],
                'hitNum' => $course['hitNum'],
                'updatedTime' => $course['updatedTime'],
                'createdTime' => $course['createdTime'],
            );
        }
        $data['resources'] = $resources;
        return $data;
    }
}