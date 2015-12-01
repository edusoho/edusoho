<?php

namespace Topxia\Api\SpecialResponse;

use Topxia\Common\ArrayToolkit;


class QiQiuYunV1ArticleResponse implements SpecialResponse
{
    public function filter($data)
    {
        $resources = array();
        foreach ($data['resources'] as $lesson) {
            $resources[] = array(
                'id' => $lesson['id'],
                'title' => $lesson['title'],
                'content' => $lesson['body'],
                'tags' => ArrayToolkit::column($lesson['tags'], 'name'),
                'category' => isset($lesson['category']['name']) ? $lesson['category']['name'] : '',
                'hitNum' => $lesson['hits'],
                'postNum' => $lesson['postNum'],
                'upsNum' => $lesson['upsNum'],
                'createdTime' => $lesson['createdTime'],
                'updatedTime' => $lesson['updatedTime'],
            );
        }

        $data['resources'] = $resources;
        return $data;
    }
}