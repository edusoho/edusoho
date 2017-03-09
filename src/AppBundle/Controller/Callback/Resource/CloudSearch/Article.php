<?php

namespace AppBundle\Controller\Callback\Resource\CloudSearch;

use AppBundle\Controller\Callback\Resource\BaseResource;

class Article extends BaseResource
{
    public function filter($res)
    {
        $res['thumb'] = $this->getFileUrl($res['thumb']);
        $res['originalThumb'] = $this->getFileUrl($res['originalThumb']);
        $res['picture'] = $this->getFileUrl($res['picture']);
        $res['body'] = $this->filterHtml($res['body']);
        $res['createdTime'] = date('c', $res['createdTime']);
        $res['updatedTime'] = date('c', $res['updatedTime']);

        $site = $this->getSettingService()->get('site', array());
        $res['source'] = isset($site['name']) ? $site['name'] : '';

        return $res;
    }
    
    /**
     * @return Biz\System\Service\SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}