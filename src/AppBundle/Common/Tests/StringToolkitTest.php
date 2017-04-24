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
}
