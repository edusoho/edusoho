<?php

namespace Tests\Unit\AppBundle\Common;

use AppBundle\Common\ChangelogToolkit;
use Biz\BaseTestCase;

class ChangeLogToolkitTest extends BaseTestCase
{
    public function testParseSingleChangelog()
    {
        $changelog = 'CHANGELOG
                        ==============
                        8.3.50（2019-11-07）
                        优化：倍速播放最小倍速调整为0.5倍；
                        优化：当班级名称过长时，在详情页被遮挡的问题；
                        修复：部分情况下，班级搜索结果不准确；';
        $res = ChangelogToolkit::parseSingleChangelog($changelog);
        $this->assertEquals('8.3.50', $res['version']);
        $this->assertEquals('2019-11-07', $res['date']);
    }
}
