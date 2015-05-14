<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Common\ArrayToolkit;
use Topxia\DataTag\UserFriendCountDataTag;

class UserFriendCountDataTagTest extends BaseTestCase
{

    public function testGetData()
    {
        $dataTag = new UserFriendCountDataTag();
        $count = $dataTag->getData(array('userId' => 1));

        $this->assertEquals(0, $count['following']);
        $this->assertEquals(0, $count['follower']);
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Article.CategoryService');
    }

}