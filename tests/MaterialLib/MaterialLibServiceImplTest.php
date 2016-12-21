<?php

namespace Topxia\Service\Course\Tests;

use Biz\BaseTestCase;

class MaterialLibServiceImplTest extends BaseTestCase
{
    protected function getMaterialLibService()
    {
        return $this->getServiceKernel()->createService('MaterialLib:MaterialLibService');
    }
}
