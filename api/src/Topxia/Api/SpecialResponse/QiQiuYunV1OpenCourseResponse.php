<?php

namespace Topxia\Api\SpecialResponse;

use AppBundle\Common\ArrayToolkit;


class QiQiuYunV1OpenCourseResponse implements SpecialResponse
{
    public function filter($data)
    {
        if (isset($data['error'])) {
            return $data;
        }

        $resources = array();
        foreach ($data['resources'] as $openCourse) {
            $resources[] = array(
                'id'          => $openCourse['id'],
                'title'       => $openCourse['title'],
                'subtitle'    => $openCourse['subtitle'],
                'type'        => 'public_' . $openCourse['type'],
                'lessonNum'   => $openCourse['lessonNum'],
                'studentNum'  => $openCourse['studentNum'],
                'hitNum'      => $openCourse['hitNum'],
                'likeNum'     => $openCourse['likeNum'],
                'postNum'     => $openCourse['postNum'],
                'tags'        => ArrayToolkit::column($openCourse['tags'], 'name'),
                'category'    => isset($openCourse['category']['name']) ? $openCourse['category']['name'] : '',
                'about'       => $openCourse['about'],
                'picture'     => $openCourse['largePicture'],
                'createdTime' => $openCourse['createdTime'],
                'updatedTime' => $openCourse['updatedTime']
            );

        }
        $data['resources'] = $resources;
        return $data;
    }
}