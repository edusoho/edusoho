<?php

namespace Topxia\Service\Course\Tests;

use Topxia\Service\User\CurrentUser;
use Topxia\Service\Common\BaseTestCase;

class MaterialLibServiceImplTest extends BaseTestCase
{
    public function testSearch()
    {
        $conditions = array();
        $start = 0;
        $limit = 10;
        $this->getMaterialLibService()->search($conditions, $start, $limit);
    }

    protected function getMaterialLibService()
    {
        return $this->getServiceKernel()->createService('MaterialLib:MaterialLib.MaterialLibService');
    }
}
