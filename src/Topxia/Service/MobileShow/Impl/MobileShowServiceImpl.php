<?php

namespace Topxia\Service\MobileShow\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\MobileShow\MobileShowService;

class MobileShowServiceImpl extends BaseService implements MobileShowService
{
	public function getMobileShow($id)
    {
        return $this->getMobileShowDao()->getMobileShow($id);
    }

    public function updateMobileShow($id, $fields)
    {
        $MobileShow = $this->getMobileShow($id);
        $showFields = array(
            'id' => $id,
            'categoryId' => $fields['categoryId'],
            'orderType' => $fields['orderType'],
            'type' => $fields['type'],
            'showCount' => $fields['showCount'],
            'title' => $fields['title'],
            'updateTime' => time()
            ); 
        return $this->getMobileShowDao()->updateMobileShow($id, $showFields);
    }

    public function deleteMobileShow($id)
    {
        $this->getMobileShowDao()->deleteMobileShow($id);
        return true;
    }

    public function addMobileShow($fields)
    {
    	return $this->getMobileShowDao()->addMobileShow($fields);
    }

    public function findMobileShowByTitle($title)
    {
        return $this->getMobileShowDao()->findMobileShowByTitle($title);
    }

    public function getAllMobileShows()
    {
        return $this->getMobileShowDao()->getAllMobileShows();
    }

	protected function getMobileShowDao()
    {
        return $this->createDao('MobileShow.MobileShowDao');
    }
}