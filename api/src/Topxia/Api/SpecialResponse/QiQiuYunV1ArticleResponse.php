<?php

namespace Topxia\Api\SpecialResponse;

use AppBundle\Common\ArrayToolkit;


class QiQiuYunV1ArticleResponse implements SpecialResponse
{
    public function filter($data)
    {
        if (isset($data['error'])) {
            return $data;
        }

        $resources = array();
        if(isset($data['resources'])){
            foreach ($data['resources'] as $article) {
                $resources[] = array(
                    'id' => $article['id'],
                    'title' => $article['title'],
                    'content' => $article['body'],
                    'tags' => ArrayToolkit::column($article['tags'], 'name'),
                    'category' => isset($article['category']['name']) ? $article['category']['name'] : '',
                    'hitNum' => $article['hits'],
                    'postNum' => $article['postNum'],
                    'upsNum' => $article['upsNum'],
                    'createdTime' => $article['createdTime'],
                    'updatedTime' => $article['updatedTime'],
                );
            }
        }

        $data['resources'] = $resources;
        return $data;
    }
}