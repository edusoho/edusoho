<?php

namespace Topxia\Service\PostFilter\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Common\KeywordFilter;

class SensitiveWordServiceTest extends BaseTestCase
{
    public function testFilterSensitiveWord()
    {
        $setting = array(
            "ignoreWord"  => "\,|\,|\.|\。",
            "wordReplace" => '{
                        "一":1,
                        "二":2,
                        "三":3,
                        "四":4
                        }',
            "firstLevel"  => "a",
            "secondLevel" => "qaf",
            "thirdLevel"  => "sfs"
        );
        $this->getSettingService()->set('sensitiveWord', $setting);

        $this->getSensitiveWordService()->filter("张三李四王武。");
    }

    public function testKeywordFilter()
    {
        $keywordFilter = new KeywordFilter();
        $keywordFilter->addKeywords(array('中国共产党'));

        $keywordFilter = new KeywordFilter();
        $keywordFilter->addKeywords(array('中国国民党'));

        $keywordFilter = new KeywordFilter();
        $keywordFilter->addKeywords(array('中国'));

        $keywordFilter->remove('中国');

        $filterResult = $keywordFilter->filter('中国共产党');

        var_dump($filterResult);
        $this->assertEquals('*****', $filterResult);
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
