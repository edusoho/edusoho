<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Common\ArrayToolkit;
use Topxia\DataTag\ArticleCategoryDataTag;

class ArticleCategoryDataTagTest extends BaseTestCase
{   
    /**
     * @group current
     * @return [type] [description]
     */
    public function testGetData()
    {

    }

    private function getGroupService()
    {
        return $this->getServiceKernel()->createService('Group.GroupService');
    }


}