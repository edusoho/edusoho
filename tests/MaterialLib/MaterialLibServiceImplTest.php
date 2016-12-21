<?php

namespace Topxia\Service\Course\Tests;

use Biz\MaterialLib\Service\MaterialLibService;
use Biz\User\CurrentUser;
use Biz\BaseTestCase;;

class MaterialLibServiceImplTest extends BaseTestCase
{
    public function testSearch()
    {
        $conditions = array();
        $start = 0;
        $limit = 10;

        //@TODO 接口里并没有这个方法
        // $this->getMaterialLibService()->seach($conditions, $start, $limit);
    }

    /**
     * @return MaterialLibService
     */
    protected function getMaterialLibService()
    {
        return $this->getServiceKernel()->createService('MaterialLib:MaterialLibService');
    }
}
