<?php

namespace Topxia\Service\PostFilter\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Common\ArrayToolkit;

class SensitiveWordServiceTest extends BaseTestCase
{
    public function testFilterSensitiveWord()
    {
        $setting = array(
            "ignoreWord" => "\,|\,|\.|\。",
            "wordReplace"=>'{
                        "一":1,
                        "二":2,
                        "三":3,
                        "四":4
                        }',
            "firstLevel"=> "a",
            "secondLevel" => "qaf",
            "thirdLevel" => "sfs"
        );
        $this->getSettingService()->set('sensitiveWord', $setting);

        $this->getSensitiveWordService()->filter("张三李四王武。");
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getSensitiveWordService()
    {
        return $this->getServiceKernel()->createService('PostFilter.SensitiveWordService');
    }

}