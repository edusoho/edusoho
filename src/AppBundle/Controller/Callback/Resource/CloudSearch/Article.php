<?php

namespace AppBundle\Controller\Callback\Resource\CloudSearch;

use AppBundle\Controller\Callback\Resource\BaseResource;
use AppBundle\Common\ArrayToolkit;

class Article extends BaseResource
{
    public function filter($res)
    {
        $filteredRes = array();

        $filteredRes['id'] = $res['id'];
        $filteredRes['title'] = $res['title'];
        $filteredRes['content'] = $this->filterHtml($res['body']);

        if (empty($res['tagIds'])) {
            $filteredRes['tagIds'] = array();
            $filteredRes['tags'] = array();
        } else {
            $filteredRes['tagIds'] =  $res['tagIds'];
            $tags = $this->getTagService()->findTagsByIds($res['tagIds']);
            $filteredRes['tags'] = ArrayToolkit::column($tags, 'name');
        }

        $filteredRes['category'] = isset($res['category']['name']) ? $res['category']['name'] : '';
        $filteredRes['hitNum'] = $res['hits'];
        $filteredRes['postNum'] = $res['postNum'];
        $filteredRes['upsNum'] = $res['upsNum'];
        $filteredRes['createdTime'] = date('c', $res['createdTime']);
        $filteredRes['updatedTime'] = date('c', $res['updatedTime']);

        return $filteredRes;
    }

    /**
     * @return Biz\System\Service\SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return Biz\Taxonomy\Service\TagService
     */
    protected function getTagService()
    {
        return $this->getBiz()->service('Taxonomy:TagService');
    }
}
