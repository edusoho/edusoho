<?php

namespace AppBundle\Common\Tests;

use Biz\BaseTestCase;
use AppBundle\Common\SimpleValidator;

class SimpleValidatorTest extends BaseTestCase
{
    public function testEmail()
    {
        $result = SimpleValidator::email('68988');
        $this->assertEquals(false, $result);
        $result = SimpleValidator::email('12@163.com');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::email('ugihui');
        $this->assertEquals(false, $result);
    }

    public function testNickname()
    {
        $result = SimpleValidator::nickname('ffgifghjfdggg', array());
        $this->assertEquals(true, $result);
        $result = SimpleValidator::nickname('s', array());
        $this->assertEquals(false, $result);
        $result = SimpleValidator::nickname('qertyuiosdfghjklxcvbnmvb', array());
        $this->assertEquals(false, $result);
    }

    public function testPassword()
    {
        $result = SimpleValidator::password('qwer', array());
        $this->assertEquals(false, $result);
        $result = SimpleValidator::password('qwert', array());
        $this->assertEquals(true, $result);
        $result = SimpleValidator::password('123456', array());
        $this->assertEquals(true, $result);
        $result = SimpleValidator::password('qwertqwertqwertqwetq', array());
        $this->assertEquals(true, $result);
        $result = SimpleValidator::password('qwertqwertqwertq2wetq', array());
        $this->assertEquals(false, $result);
    }

    public function testTruename()
    {
        $result = SimpleValidator::truename('制');
        $this->assertEquals(false, $result);
        $result = SimpleValidator::truename('限制一个到');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::truename('限制一个到1');
        $this->assertEquals(false, $result);
        $result = SimpleValidator::truename('ty');
        $this->assertEquals(false, $result);
    }

    public function testIdcard()
    {
        $result = SimpleValidator::idcard('331082199307178894');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::idcard('33108219930717889x');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::idcard('33108219930178894');
        $this->assertEquals(false, $result);
        $result = SimpleValidator::idcard('33108x199307178894');
        $this->assertEquals(false, $result);
        $result = SimpleValidator::idcard('33108219930717889X');
        $this->assertEquals(true, $result);
    }

    public function testBankCardId()
    {
        $result = SimpleValidator::bankCardId('1234567890123456');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::bankCardId('1234567890123456789');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::bankCardId('12345678901234');
        $this->assertEquals(false, $result);
        $result = SimpleValidator::bankCardId('12345678901234567ww');
        $this->assertEquals(false, $result);
    }

    public function testMobile()
    {
        $result = SimpleValidator::mobile('12345678990');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::mobile('22345678990');
        $this->assertEquals(false, $result);
        $result = SimpleValidator::mobile('123456789900');
        $this->assertEquals(false, $result);
        $result = SimpleValidator::mobile('12345678x90');
        $this->assertEquals(false, $result);
    }

    public function testNumbers()
    {
        $result = SimpleValidator::numbers('546');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::numbers('5x6');
        $this->assertEquals(false, $result);
        $result = SimpleValidator::numbers('z46');
        $this->assertEquals(false, $result);
        $result = SimpleValidator::numbers('54c');
        $this->assertEquals(false, $result);
        $result = SimpleValidator::numbers('sss');
        $this->assertEquals(false, $result);
        $result = SimpleValidator::numbers('3,111,000');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::numbers('3,029,000,');
        $this->assertEquals(false, $result);
    }

    //固定电话号码
    public function testPhone()
    {
        $result = SimpleValidator::phone('157-57125300');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::phone('0571-5712530');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::phone('010-1234567');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::phone('021-12345678');
        $this->assertEquals(true, $result);
        //手机号
        $result = SimpleValidator::phone('15757125300');
        $this->assertEquals(false, $result);
        $result = SimpleValidator::phone('15757-125301');
        $this->assertEquals(false, $result);
        $result = SimpleValidator::phone('1575-7125301111');
        $this->assertEquals(false, $result);
    }

    public function testDate()
    {
        $result = SimpleValidator::date('2014-01-13');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::date('14-01-13');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::date('2014-01-31');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::date('2014-1-31');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::date('2014-35-13');
        $this->assertEquals(false, $result);
        $result = SimpleValidator::date('2014-02-32');
        $this->assertEquals(false, $result);
        $result = SimpleValidator::date('4-35-13');
        $this->assertEquals(false, $result);
    }

    public function testQq()
    {
        $result = SimpleValidator::qq('12341');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::qq('123');
        $this->assertEquals(false, $result);
        $result = SimpleValidator::qq('12325674345');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::qq('1223xx');
        $this->assertEquals(false, $result);
    }

    public function testInteger()
    {
        $result = SimpleValidator::integer('-9');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::integer('123456');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::integer('+9');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::integer('9');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::integer('9.1');
        $this->assertEquals(false, $result);
        $result = SimpleValidator::integer('900000000000000000');
        $this->assertEquals(false, $result);
    }

    public function testFloat()
    {
        $result = SimpleValidator::float('-1');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::float('+1.1');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::float('-1.2222');
        $this->assertEquals(false, $result);
        $result = SimpleValidator::float('-100.2');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::float('-1009.2');
        $this->assertEquals(true, $result);
    }

    public function testDateTime()
    {
        $result = SimpleValidator::dateTime('1993-09-20');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::dateTime('2000-02-29');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::dateTime('1900-02-29');
        $this->assertEquals(false, $result);
        $result = SimpleValidator::dateTime('0000-02-29');
        $this->assertEquals(false, $result);
        $result = SimpleValidator::dateTime('0000-4-31');
        $this->assertEquals(false, $result);
    }

    public function testSite()
    {
        $result = SimpleValidator::site('http://www.google.com/');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::site('http://www.ba1du.com/');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::site('http://e2/we#&/');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::site('https://e2/we#&/');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::site('http://e2we#&/');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::site('http://e2we#&');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::site('http://ew2/we#&/');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::site('ftp://www.ba1du.com/');
        $this->assertEquals(false, $result);
        $result = SimpleValidator::site('www.g.com/');
        $this->assertEquals(false, $result);
    }

    public function testChineseAndAlphanumeric()
    {
        $result = SimpleValidator::chineseAndAlphanumeric('我是火车王');
        $this->assertEquals(true, $result);
        $result = SimpleValidator::chineseAndAlphanumeric('<<<信仰圣光吧>>>');
        $this->assertEquals(false, $result);
        $result = SimpleValidator::chineseAndAlphanumeric('en$gl%ish!');
        $this->assertEquals(false, $result);
        $result = SimpleValidator::chineseAndAlphanumeric('中文_english_');
        $this->assertEquals(true, $result);
    }
}
