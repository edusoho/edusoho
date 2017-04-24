<?php

namespace Topxia\Api\SpecialResponse;

use AppBundle\Common\ArrayToolkit;


class QiQiuYunV1CourseResponse implements SpecialResponse
{
    public function filter($data)
    {
        if (isset($data['error'])) {
            return $data;
        }
        
        $resources = array();
        foreach ($data['resources'] as $course) {
            $resources[] = array(
                'id' => $course['id'],
                'title' => $course['title'],
                'subtitle' => $course['subtitle'],
                'type' => $course['type'],
                'price' => $course['price'],
                'lessonNum' => $course['lessonNum'],
                'rating' => $course['rating'],
                'ratingNum' => $course['ratingNum'],
                'tags' => $course['tags'],
                'category' => isset($course['category']['name']) ? $course['category']['name'] : '',
                'about' => $course['about'],
                'goals' => $course['goals'],
                'picture' => $course['largePicture'],
                'audiences' => $course['audiences'],
                'hitNum' => $course['hitNum'],
                'createdTime' => $course['createdTime'],
                'updatedTime' => $course['updatedTime'],
            );

        }
        $data['resources'] = $resources;
        return $data;
    }
}