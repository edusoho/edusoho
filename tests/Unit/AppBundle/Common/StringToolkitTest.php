<?php

namespace AppBundle\Common\Tests;

use Biz\BaseTestCase;
use AppBundle\Common\StringToolkit;

class StringToolkitTest extends BaseTestCase
{
    public function testTemplate()    //替换字符串中的模板
    {
        $message = '{{item}}'.'++++'.'前面的item里的内容是可更换的';
        $variables = array('item' => '我');
        $message = StringToolkit::template($message, $variables);
        $this->assertEquals($message, '我++++前面的item里的内容是可更换的');

        //测试特殊字符
        $message = '{{item}}'.'++++'.'前面的item里的内容是可更换的';
        $variables = array('item' => '!@#$%^&*()-+~`1234567890');
        $message = StringToolkit::template($message, $variables);
        $this->assertEquals($message, '!@#$%^&*()-+~`1234567890++++前面的item里的内容是可更换的');
    }

    public function testTemplateWithHTML()    //替换字符串中的HTML
    {
        $message = '{{item}}'.'++++'.'前面的item里的内容是可更换的';
        $variables = array('item' => '<a>&nbsp</a>');
        $message = StringToolkit::template($message, $variables);
        $this->assertEquals($message, '<a>&nbsp</a>++++前面的item里的内容是可更换的');
    }

    public function testTemplateWithEmptyVariables()    //空变量返回原来的字符串
    {
        $message = '{{item}}'.'++++'.'前面的item里的内容是可更换的';
        $variables = array();
        $messageResult = StringToolkit::template($message, $variables);
        $this->assertEquals($message, $messageResult);
    }

    public function testSign()    //加密
    {
        $key = '1';
        $data = array('1');
        $message = StringToolkit::sign($data, $key);
        $this->assertEquals($message, md5(json_encode($data).$key));
    }

    public function testSecondsToTextToHour()    //字符串中的数字当做秒来转换成一小时内的时间
    {//方法不能识别所传的是分还是秒
        $time = '3599'; //字符串中的的数字不应大于3599
        $result = StringToolkit::secondsToText($time);
        $this->assertEquals($result, '59:59');
    }

    public function testSecondsToTextToDay()    //字符串中的时间当做分来转换成一天的时间,
    {//方法不能识别所传的是分还是秒
        $time = '1439'; //字符串中的的数字不应大于1439
        $result = StringToolkit::secondsToText($time);
        $this->assertEquals($result, '23:59');
    }

    public function testTextToSeconds()    //只能传一小时内的数据
    {
        $stringTime = '14:23';
        $result = StringToolkit::textToSeconds($stringTime);
        $this->assertEquals($result, '863');
    }

    public function testTextToSecondsTwice()    //只能传一小时内的数据,多了没用
    {
        $stringTime = '14:23:23';
        $result = StringToolkit::textToSeconds($stringTime);
        $this->assertEquals($result, '863');
    }

    public function testTextToSecondsWidthEmptycolon()    //只能传一小时内的数据,多了没用
    {
        $stringTime = '101231';
        $result = StringToolkit::textToSeconds($stringTime);
        $this->assertEquals($result, '0');
    }

    public function testPlain()
    {
        $string = '&nbsp;'."\r"."\n";
        $result = StringToolkit::plain($string);
        $this->assertEquals($result, '');
    }

    public function testPlainTwice()    //字符串截取第0-x位
    {
        $string = '&nbsp;'."\r"."\n".'EduSoho网校系统';
        $result = StringToolkit::plain($string, '5');    //0-5位
        $this->assertEquals($result, 'EduSo...');
    }

    public function testJsonPettryWithEmptyString()
    {
        $string = '';
        $result = StringToolkit::jsonPettry($string);
        $this->assertEquals($result, '');
    }

    public function testJsonPettryWithOneChar()
    {
        $string = 'x';
        $result = StringToolkit::jsonPettry($string);
        $this->assertEquals($result, $string);
    }

    public function testJsonPettryWithArray()
    {
        $array = array('a' => '1', 'b' => '2', 'c' => '3');
        $jstring = json_encode($array);
        $jstring = '['.$jstring.']';
        $result = StringToolkit::jsonPettry($jstring);
        $this->assertGreaterThan($result, $jstring);
    }

    public function testJsonPettryWithString()
    {
        $string = "hello,I'm stefaine.";
        $jstring = json_encode($string);
        $result = StringToolkit::jsonPettry($jstring);
        $this->assertEquals($jstring, $result);
    }

    public function testCompress()
    {
        $m3u8Content = '#EXTM3U
        #EXT-X-VERSION:3
        #EXT-X-TARGETDURATION:16
        #EXT-X-ALLOW-CACHE:YES
        #EXT-X-MEDIA-SEQUENCE:0
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0x2d7c2a6edbfd0850b0d1a50ade327c60
        #EXTINF:11.800,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_0_ehls_950a2d?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0xcd8db44a7bbba0e515e769d1820f45b4
        #EXTINF:10.000,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_1_ehls_07702f?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0xca940f18e860492d8115d15d3f0a8889
        #EXTINF:14.760,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_2_ehls_90ea9b?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0x49bd808bc7652ae95d211674070ea9da
        #EXTINF:4.200,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_3_ehls_69fbba?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0xddbf6f0743e6f9c0ec5cb56a79501b00
        #EXTINF:10.600,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_4_ehls_66e709?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0xfc20cb3b197b0956836db330b8b53fbb
        #EXTINF:9.600,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_5_ehls_4f1742?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0xb7f59ab518ae25569019b516e8bb21c8
        #EXTINF:10.560,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_6_ehls_a0b9ea?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0x340cd5ec5a3c6f2c3fc0a2032c0c8a5b
        #EXTINF:15.520,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_7_ehls_92336f?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0x88281fe24da98bb89f4f654dcc4b9bc2
        #EXTINF:8.040,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_8_ehls_62a974?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0x9e10fa187cda315d89e12b93de6986f7
        #EXTINF:10.000,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_9_ehls_49681a?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0xb8b0f749e710c75263d5aef793e6ad1d
        #EXTINF:10.000,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_10_ehls_9c708c?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0x4e0621cebf357c6e4437b6e4a02f9a99
        #EXTINF:10.000,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_11_ehls_0f3963?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0x9375996e3fd3730c6766ea6144efbc2c
        #EXTINF:5.800,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_12_ehls_ab1819?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0x21da537a6205070af2888317aebbaef4
        #EXTINF:10.640,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_13_ehls_d49209?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0xf4ff9f58b8c83b9ea705f5258293336f
        #EXTINF:9.000,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_14_ehls_fb59bb?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0xdf7a72a4bb4aca51c398e3f4ed7d667f
        #EXTINF:13.280,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_15_ehls_3fc33a?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0x4fbaee43778390b326958b3d23ed355e
        #EXTINF:13.040,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_16_ehls_9920b6?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0x8caccd39e9dc320bb3ccc687ceb24a30
        #EXTINF:5.920,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_17_ehls_10814b?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0x0105a6f11606cd18a76bbd2bba13fa0b
        #EXTINF:11.080,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_18_ehls_cf891a?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0x2ff5d142a1d5da93f879b413be7ea473
        #EXTINF:7.240,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_19_ehls_33960e?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0x76d5ceb3e8305a2f504eb2624db62fbb
        #EXTINF:9.480,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_20_ehls_830a2e?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0x0d80629a8f7001cca01a3f4c16d007ad
        #EXTINF:13.600,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_21_ehls_c638cc?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0x612c6ae2a8329460545b254772e2e269
        #EXTINF:7.440,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_22_ehls_c35db9?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0x7c882bffaf5db1e6b876ea9655b93861
        #EXTINF:9.600,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_23_ehls_d24438?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0xc89ca249e2da40ada2fc304adea9dde4
        #EXTINF:8.800,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_24_ehls_21b5b8?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0x8b76378ce3c5415f9befbd52294d1558
        #EXTINF:11.040,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_25_ehls_8357f9?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0x20c728c27c3f5d91722b7b05ce14a596
        #EXTINF:11.600,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_26_ehls_b50f7f?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0xca865bf0c49f5c9ecc96865c28584752
        #EXTINF:11.040,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_27_ehls_cb22bc?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0x009e7fc61e93d1a67eee57ab8d5b7d8f
        #EXTINF:10.000,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_28_ehls_312364?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0x8d16d3414afa3d15d1f31f6c55986c47
        #EXTINF:8.200,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_29_ehls_9fe775?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-KEY:METHOD=AES-128,URI="http://try6.edusoho.cn/hls/4813/clef/WHb7i4BoChAgoqRKdOZCIXQVxSkenUx2",IV=0xfb6de978a23db6ca038787b4417d0557
        #EXTINF:8.800,
        http://ese2a3b1c3d2dp-pub.pub.qiqiuyun.net/23132/8f0a27ad00e644888035ff6d75b72a19/lnTQrQx37fjGrJxc-sd_seg_30_ehls_24f68b?schoolId=23132&fileGlobalId=8f0a27ad00e644888035ff6d75b72a19
        #EXT-X-ENDLIST';

        $result = StringToolkit::compress($m3u8Content);
        $compressRate = strlen($result) / strlen($m3u8Content);

        if (StringToolkit::isCompressable()) {
            $this->assertTrue($compressRate < 0.13);
        } else {
            $this->assertTrue(1 == $compressRate);
        }
    }

    public function testUncompress()
    {
        $base64EncodedCompressContent = '1ZpZb1RHEEbf8yuiIOUJz9TStbQlFDlmAg6b8EJIXlCvMYllAwbJ/Pu0wyVSmJHyEulyvciaey1PzVH56zrdc2fz8vQJn33z7fRxZzzee7n3YnN8cvTs6T5/eeP04PjB5vT+2fHB6e191C9/4eDx42e/7B0eHD7c7P+6Ofny9pPN/aODvZPN87PN08PNPnx5/9Hm1/0nm9OHz+7fO9ic7CH53bPjo3vfnb9//2Z/vX7/7qOuWv1wfXV+tSqX6/OL63Vw5HW5aH39y8Nsr8OPV4fnB79fvT1+VJ/9dnj08vmLm5M/2+XZDX139+jFPbihaoWStpp7BRfIUDEJpNqYrOi/azp6+tM+4soB7v5zfSqmXTdKnLFwpfpm782HvLr9fvv67esPHz9cri7b+zUxMq29QyJLFaBpCO4OLL1rNclGCeP64vL0+bvnN2z9jwfvfr4pe9f11XX7/RW8auMlvoqjOqo/XJfzq6uLo3rv77/6fX990R5cXOV0e+m/nmIO0KV6zSEkyzknaILSTGNFJ+hBctgGDSuYBzR+Ag1mQH15oFMM0NGbK4RI1RGlji8etY4K4zbosDKdBTRNHQ0txbw40CHm6uC5mAqlFqUSoloAu309NW2BDiuap6H5E2eNffzvLY5zHdmsHSxw0x4LtCIliyYbSYgZdkQ0rHQe0GECrc0gLg50LwQlD0LRMkRRZ62ZGbJn4dE6W6DjXJzlE+fQ0QItjnO2LjFlQU+NRDQCxvFIm+dMWHxXQ8s8Ca2fQCfIsS0vOThAqTLyInHRToV7GUUCU4HiSbYbGmUlNAtom5ZCYtblzRzu5NgbhZriaGKPPXSVUEsJOeZCW6B9BWEWzj4lNKVoYXGcY0PoCd1KTTxmOh8XKEeuTaNrt69oiI5TREd1XF5yjCUPuoXYDKGYkHKV1LrFMYOkivUrAo2fvbAYeFkc6dBAx6LXcmcZwt1CYMvjRxruFVPcoSvzkf4shp2j8uJIRzaJURv3ysZQ1MacmhRDaH1kdNkiLXPtdOAkhimj4/LmaMKahC0pgQwZTJ1GXYyW2tCv1ndudeg8yyFOalhDpCUaS+g9dvHsxfl2RDWQLiROkW8HqR3GMld2TGrYs8S8vL2O2i2NJw85h1SSDFLRR5CEVq2q2jZo5BX5PKQnORzDPvPyJo/QR0a0sQiac4TMpHH098DFrbJI20V6plkaJzuMIzuyLo60l1RK5dhiLTxeQeZSio7ZumUKibf3lWQV55FDnOwQwTEsLzwAQZJ2RAUtFT2Z5lxpLIbIPcEODccVzBQekx+W7nGB2kK9S8Uwnr7KUHHubjEH5NyspWC8RdpWNFN2TH7IY5SGtjjQplVGTnBzHr1NXSCM1FAKNSvt3ioN83Q0TXo4Ck20PNBQffhhTN4NAEtJgGnMHQV1lDlq3bUczrQpTZMeFmUvyxNxRSqaBiVnikFBgmSSYEZtfOq2iNsqzJMdNOlhYal5edJixZ1y72lkdcam2W14eFSRHNkVd2THXB392Q4pBPbFgS4eS6IQG9UUINWR04UhpHp7PFvbtof7XBseNNkhYZa8PNCeTdm8NC4SUHrMrecqNHKkosiOAy2cy1lIPi+HYn152UFQjLyQFR7pEdGIsmUYswiGJFF3kZ4rPCY7zALdlnekVZKr5A4lxC4ltlKijiuFXDyYbB9pzdjTkx6WPLpheYMHQGzWi2KLXDGptdbEUvY6iqq+Y29pthMAmvSQkViXd3zodYzNHEZU9MS37wnDzti1iETXEraPD32udyzRpIexNzNZHOietbZonoiHEA5pYTe3HAJaBZFdoGeaPHjSQwpd/f/dWto8vf/46OT0Lw==';
        $result = StringToolkit::uncompress(base64_decode($base64EncodedCompressContent));
        $this->assertEquals(1848, strlen($base64EncodedCompressContent));

        if (StringToolkit::isCompressable()) {
            $this->assertEquals(11350, strlen($result));
        } else {
            $this->assertEquals(1384, strlen($result));
        }
    }
}
