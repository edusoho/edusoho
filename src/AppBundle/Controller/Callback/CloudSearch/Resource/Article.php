<?php

namespace AppBundle\Controller\Callback\CloudSearch\Resource;

use AppBundle\Controller\Callback\CloudSearch\BaseResource;
use AppBundle\Common\ArrayToolkit;

class Article extends BaseResource
{
    public function filter($res)
    {
        $filteredRes = array();

        $filteredRes['id'] = $res['id'];
        $filteredRes['title'] = $res['title'];
        $filteredRes['content'] = $this->filterHtml($res['body']);
        $filteredRes['tags'] = empty($res['tags']) ? array() : ArrayToolkit::column($res['tags'], 'name');
        $filteredRes['category'] = isset($res['category']['name']) ? $res['category']['name'] : '';
        $filteredRes['hitNum'] = $res['hits'];
        $filteredRes['postNum'] = $res['postNum'];
        $filteredRes['upsNum'] = $res['upsNum'];
        $filteredRes['createdTime'] = date('c', $res['createdTime']);
        $filteredRes['updatedTime'] = date('c', $res['updatedTime']);

        return $filteredRes;
    }
}
