<?php

namespace Tests\Unit\Common;

use Biz\BaseTestCase;

class HTMLHelperTest extends BaseTestCase
{
    public function testPurify()
    {
        //1.传入为空
        $biz = $this->getBiz();
        $htmlHelper = $biz['html_helper'];
        $result = $htmlHelper->purify(null, false);
        $this->assertEmpty($result);

        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('safe_iframe_domains' => array(
                        'www.baidu.com',
                    )),
                ),
            )
        );
        //2.传入不带任何需要过滤的字符串
        $result = $htmlHelper->purify('HTMLHelperTest', false);
        $this->assertEquals('HTMLHelperTest', $result);

        //3.1传入带有script
        $result = $htmlHelper->purify('HTMLHelperTest<script>alert(1)</script>', false);
        $this->assertEquals('HTMLHelperTest', $result);

        //3.2传入带有style（过滤style）
        $result = $htmlHelper->purify('HTMLHelperTest<style>.test{height:15px;}</style>', false);
        $this->assertEquals('HTMLHelperTest', $result);

        //3.3传入带有style（不过滤style）
        $result = $htmlHelper->purify('HTMLHelperTest<style>.test{height:15px;}</style>', true);

        $this->assertEquals('<style type="text/css">
.test {
height:15px
}
</style>
HTMLHelperTest', $result);

        //4.不符合链接地址的src(这块为自带的purify方法处理)、外链但不在白名单内的链接，自动过滤（这块为正则表达式处理）
        $html = '=<img alt="" src="/file/test.jpg" />=<img alt="" src="httpsss://httpbin.org/basic-auth/user/passwd" />=<img alt="" src="https://httpbin.org/basic-auth/user/passwd" />=<img alt="" src="http://www.baidu.com/basic-auth/user/passwd" />=';
        $result = $htmlHelper->purify($html, false);
        $this->assertEquals('=<img alt="" src="/file/test.jpg" />===<img alt="" src="http://www.baidu.com/basic-auth/user/passwd" />=', $result);
    }
}
