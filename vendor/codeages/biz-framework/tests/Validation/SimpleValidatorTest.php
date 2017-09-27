<?php

namespace Tests\Validation;

use Codeages\Biz\Framework\Validation\SimpleValidator;
use PHPUnit\Framework\TestCase;

class SimpleValidatorTest extends TestCase
{
    public function testValidate_Required_Pass()
    {
        $v = new SimpleValidator();
        $td = array('foo' => 'bar');
        $rules = array('foo' => 'required');
        $vd = $v->validate($td, $rules);
        $this->assertEquals($td, $vd);
    }

    public function testValidate_Required_Failed()
    {
        $v = new SimpleValidator();
        $td = array('foo' => 'bar');
        $rules = array('bar' => 'required');
        $vd = $v->validate($td, $rules, false);
        $this->assertNull($vd);
        $this->assertCount(1, $v->errors());

        $v = new SimpleValidator();
        $td = array('foo' => '');
        $rules = array('foo' => 'required');
        $vd = $v->validate($td, $rules, false);
        $this->assertNull($vd);
        $this->assertCount(1, $v->errors());

        $v = new SimpleValidator();
        $td = array('foo' => '  ');
        $rules = array('foo' => 'required');
        $vd = $v->validate($td, $rules, false);
        $this->assertNull($vd);
        $this->assertCount(1, $v->errors());

        $v = new SimpleValidator();
        $td = array('foo' => null);
        $rules = array('foo' => 'required');
        $vd = $v->validate($td, $rules, false);
        $this->assertNull($vd);
        $this->assertCount(1, $v->errors());
    }

    public function testValidate_Numeric_Pass()
    {
        $v = new SimpleValidator();
        $td = array(
            'foo1' => '0',
            'foo2' => 0,
            'foo3' => '1',
            'foo4' => 1,
            'foo5' => '-1',
            'foo6' => -1,
            'foo7' => '1.1',
            'foo8' => 1.1,
            'foo9' => '.1',
        );
        $rules = array(
            'foo1' => 'numeric',
            'foo2' => 'numeric',
            'foo3' => 'numeric',
            'foo4' => 'numeric',
            'foo5' => 'numeric',
            'foo6' => 'numeric',
            'foo7' => 'numeric',
            'foo8' => 'numeric',
            'foo9' => 'numeric',
        );

        $vd = $v->validate($td, $rules);

        $this->assertEquals($td, $vd);
    }

    public function testValidate_Numeric_Failed()
    {
        $v = new SimpleValidator();
        $td = array(
            'foo1' => 'abc',
            'foo2' => '.',
        );
        $rules = array(
            'foo1' => 'numeric',
            'foo2' => 'numeric',
        );

        $vd = $v->validate($td, $rules, false);
        $this->assertNull($vd);
        $this->assertCount(2, $v->errors());
    }

    public function testValidate_Int_Pass()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => '0',
            'foo2' => 0,
            'foo3' => '1',
            'foo4' => 1,
            'foo5' => '-1',
            'foo6' => -1,
        );

        $rules = array(
            'foo1' => 'integer',
            'foo2' => 'integer',
            'foo3' => 'integer',
            'foo4' => 'integer',
            'foo5' => 'integer',
            'foo6' => 'integer',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($td, $vd);
    }

    public function testValidate_Int_Failed()
    {
        $td = array(
            'foo1' => 'abc',
            'foo2' => '1.0',
        );

        $v = new SimpleValidator();
        $vd = $v->validate($td, array(
            'foo1' => 'integer',
            'foo2' => 'integer',
        ), false);

        $this->assertNull($vd);
        $this->assertCount(2, $v->errors());
    }

    public function testValidate_Float_Pass()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => '0.1',
            'foo2' => 0.1,
            'foo3' => '.0',
            'foo4' => .0,
            'foo5' => '1',
            'foo6' => 1,
            'foo7' => '-1',
            'foo8' => -1,
            'foo9' => '0.10',
        );

        $rules = array(
            'foo1' => 'float:2',
            'foo2' => 'float:2',
            'foo3' => 'float:2',
            'foo4' => 'float:2',
            'foo5' => 'float:2',
            'foo6' => 'float:2',
            'foo7' => 'float:2',
            'foo8' => 'float:2',
            'foo9' => 'float:2',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($td, $vd);
    }

    public function testValidate_Float_Failed()
    {
        $td = array(
            'foo1' => '0.123',
            'foo2' => '123A.1',
            'foo3' => '-0.123',
        );

        $v = new SimpleValidator();
        $vd = $v->validate($td, array(
            'foo1' => 'float:2',
            'foo2' => 'float:2',
            'foo3' => 'float:2',
        ), false);

        $this->assertNull($vd);
        $this->assertCount(3, $v->errors());
    }

    public function testValidate_Array_Pass()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => array(),
            'foo2' => array('1', '2', '3'),
        );

        $rules = array(
            'foo1' => 'array',
            'foo2' => 'array',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($td, $vd);
    }

    public function testValidate_Array_Failed()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => 'abc',
            'foo2' => 123,
        );

        $rules = array(
            'foo1' => 'array',
            'foo2' => 'array',
        );

        $vd = $v->validate($td, $rules, false);
        $this->assertNull($vd);
        $this->assertCount(count($td), $v->errors());
    }

    public function testValidate_Alpha_Pass()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => 'abc',
            'foo2' => 'abcABC',
        );

        $rules = array(
            'foo1' => 'alpha',
            'foo2' => 'alpha',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($td, $vd);
    }

    public function testValidate_Alpha_Failed()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => 'abc abc',
            'foo2' => 'abc123',
        );

        $rules = array(
            'foo1' => 'alpha',
            'foo2' => 'alpha',
        );

        $vd = $v->validate($td, $rules, false);
        $this->assertNull($vd);
        $this->assertCount(2, $v->errors());
    }

    public function testValidate_AlphaNum_Pass()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => 'abc',
            'foo2' => 'abcABC',
            'foo3' => 'abcABC123',
        );

        $rules = array(
            'foo1' => 'alpha_num',
            'foo2' => 'alpha_num',
            'foo3' => 'alpha_num',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($td, $vd);
    }

    public function testValidate_AlphaNum_Failed()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => 'abc 123',
            'foo2' => 'abc123_',
        );

        $rules = array(
            'foo1' => 'alpha_num',
            'foo2' => 'alpha_num',
        );

        $vd = $v->validate($td, $rules, false);
        $this->assertNull($vd);
        $this->assertCount(2, $v->errors());
    }

    public function testValidate_AlphaDash_Pass()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => 'abc_123-123',
            'foo2' => '_abc_123-123',
            'foo3' => '-abc_123-123',
        );

        $rules = array(
            'foo1' => 'alpha_dash',
            'foo2' => 'alpha_dash',
            'foo3' => 'alpha_dash',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($td, $vd);
    }

    public function testValidate_AlphaDash_Failed()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => 'abc 123',
            'foo2' => 'abc.123',
        );

        $rules = array(
            'foo1' => 'alpha_dash',
            'foo2' => 'alpha_dash',
        );

        $vd = $v->validate($td, $rules, false);
        $this->assertNull($vd);
        $this->assertCount(2, $v->errors());
    }

    public function testValidate_Digits_Pass()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => '1',
            'foo2' => '12',
            'foo3' => '123',
            'foo4' => 123,
        );

        $rules = array(
            'foo1' => 'digits:1',
            'foo2' => 'digits:2',
            'foo3' => 'digits:3',
            'foo4' => 'digits:3',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($td, $vd);
    }

    public function testValidate_Digits_Failed()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => '123',
            'foo2' => '12',
            'foo3' => '123a',
            'foo4' => '123a',
            'foo5' => 123,
        );

        $rules = array(
            'foo1' => 'digits:1',
            'foo2' => 'digits:3',
            'foo3' => 'digits:3',
            'foo4' => 'digits:4',
            'foo5' => 'digits:4',
        );

        $vd = $v->validate($td, $rules, false);
        $this->assertNull($vd);
        $this->assertCount(5, $v->errors());
    }

    public function testValidate_DigitsBetween_Pass()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => '1',
            'foo2' => '12',
            'foo3' => '123',
            'foo4' => 123,
        );

        $rules = array(
            'foo1' => 'digits_between:1,3',
            'foo2' => 'digits_between:1,3',
            'foo3' => 'digits_between:1,3',
            'foo4' => 'digits_between:1,3',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($td, $vd);
    }

    public function testValidate_DigitsBetween_Failed()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => '1',
            'foo2' => '12345',
            'foo3' => '123a',
            'foo4' => 12345,
        );

        $rules = array(
            'foo1' => 'digits_between:2,4',
            'foo2' => 'digits_between:2,4',
            'foo3' => 'digits_between:2,4',
            'foo4' => 'digits_between:2,4',
        );

        $vd = $v->validate($td, $rules, false);
        $this->assertNull($vd);
        $this->assertCount(4, $v->errors());
    }

    public function testValidate_Min_Pass()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => '10',
            'foo2' => '9.9',
            'foo3' => '9',
            'foo4' => 9,
        );

        $rules = array(
            'foo1' => 'min:9',
            'foo2' => 'min:9',
            'foo3' => 'min:9',
            'foo4' => 'min:9',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($td, $vd);
    }

    public function testValidate_Min_Failed()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => '10',
            'foo2' => '9.9',
        );

        $rules = array(
            'foo1' => 'min:11',
            'foo2' => 'min:11',
        );

        $vd = $v->validate($td, $rules, false);
        $this->assertNull($vd);
        $this->assertCount(2, $v->errors());
    }

    public function testValidate_Max_Pass()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => '10',
            'foo2' => '9.9',
            'foo3' => '20',
            'foo4' => 20,
        );

        $rules = array(
            'foo1' => 'max:20',
            'foo2' => 'max:20',
            'foo3' => 'max:20',
            'foo4' => 'max:20',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($td, $vd);
    }

    public function testValidate_Max_Failed()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => '10',
            'foo2' => '9.9',
        );

        $rules = array(
            'foo1' => 'max:5',
            'foo2' => 'max:5',
        );

        $vd = $v->validate($td, $rules, false);
        $this->assertNull($vd);
        $this->assertCount(2, $v->errors());
    }

    public function testValidate_Between_Pass()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => '10',
            'foo2' => '20',
            'foo3' => '15',
            'foo4' => 15,
        );

        $rules = array(
            'foo1' => 'between:10,20',
            'foo2' => 'between:10,20',
            'foo3' => 'between:10,20',
            'foo4' => 'between:10,20',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($td, $vd);
    }

    public function testValidate_Between_Failed()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => '9',
            'foo2' => '9.9',
            'foo3' => 9.99,
            'foo4' => 20.01,
            'foo5' => '1a',
        );

        $rules = array(
            'foo1' => 'between:10,20',
            'foo2' => 'between:10,20',
            'foo3' => 'between:10,20',
            'foo4' => 'between:10,20',
            'foo5' => 'between:10,20',
        );

        $vd = $v->validate($td, $rules, false);
        $this->assertNull($vd);
        $this->assertCount(5, $v->errors());
    }

    public function testValidate_Boolean_Pass()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => true,
            'foo2' => false,
            'foo3' => 0,
            'foo4' => 1,
            'foo5' => '0',
            'foo6' => '1',
        );

        $rules = array(
            'foo1' => 'boolean',
            'foo2' => 'boolean',
            'foo3' => 'boolean',
            'foo4' => 'boolean',
            'foo5' => 'boolean',
            'foo6' => 'boolean',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($td, $vd);
    }

    public function testValidate_Length_Pass()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => '12345',
        );

        $rules = array(
            'foo1' => 'length:5',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($td, $vd);
    }

    public function testValidate_Length_Failed()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => '123456',
            'foo2' => '1234',
        );

        $rules = array(
            'foo1' => 'length:5',
            'foo2' => 'length:5',
        );

        $vd = $v->validate($td, $rules, false);
        $this->assertNull($vd);
        $this->assertCount(2, $v->errors());
    }

    public function testValidate_LengthMin_Pass()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => '12345',
            'foo2' => '123456',
        );

        $rules = array(
            'foo1' => 'length_min:5',
            'foo2' => 'length_min:5',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($td, $vd);
    }

    public function testValidate_LengthMin_Failed()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => '1234',
        );

        $rules = array(
            'foo1' => 'length_min:5',
        );

        $vd = $v->validate($td, $rules, false);
        $this->assertNull($vd);
        $this->assertCount(count($td), $v->errors());
    }

    public function testValidate_LengthMax_Pass()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => '12345',
            'foo2' => '1234',
            'foo3' => '',
        );

        $rules = array(
            'foo1' => 'length_max:5',
            'foo2' => 'length_max:5',
            'foo3' => 'length_max:5',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($td, $vd);
    }

    public function testValidate_LengthMax_Failed()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => '123456',
        );

        $rules = array(
            'foo1' => 'length_max:5',
        );

        $vd = $v->validate($td, $rules, false);
        $this->assertNull($vd);
        $this->assertCount(count($td), $v->errors());
    }

    public function testValidate_LengthBetween_Pass()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => '12345',
            'foo2' => '1234',
            'foo3' => '123',
        );

        $rules = array(
            'foo1' => 'length_between:3,5',
            'foo2' => 'length_between:3,5',
            'foo3' => 'length_between:3,5',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($td, $vd);
    }

    public function testValidate_LengthBetween_Failed()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => '123456',
            'foo2' => '12',
        );

        $rules = array(
            'foo1' => 'length_between:3,5',
            'foo2' => 'length_between:3,5',
        );

        $vd = $v->validate($td, $rules, false);
        $this->assertNull($vd);
        $this->assertCount(count($td), $v->errors());
    }

    public function testValidate_Boolean_Failed()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => 'true',
            'foo2' => 'false',
            'foo3' => 'yes',
            'foo4' => 'no',
            'foo5' => 'on',
            'foo6' => 'off',
            'foo7' => ' ',
        );

        $rules = array(
            'foo1' => 'boolean',
            'foo2' => 'boolean',
            'foo3' => 'boolean',
            'foo4' => 'boolean',
            'foo5' => 'boolean',
            'foo6' => 'boolean',
            'foo7' => 'boolean',
        );

        $vd = $v->validate($td, $rules, false);
        $this->assertNull($vd);
        $this->assertCount(7, $v->errors());
    }

    public function testValidate_Email_Pass()
    {
        $v = new SimpleValidator();
        $td = array(
            'foo' => 'test@example.com',
        );
        $rules = array(
            'foo' => 'email',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($td, $vd);
    }

    public function testValidate_Email_Failed()
    {
        $v = new SimpleValidator();
        $td = array(
            'foo1' => 'test',
            'foo2' => 'test@',
            'foo3' => 'test@test',
            'foo4' => '@test',
            'foo5' => '@test.com',
            'foo6' => 'test.com',
        );
        $rules = array(
            'foo1' => 'email',
            'foo2' => 'email',
            'foo3' => 'email',
            'foo4' => 'email',
            'foo5' => 'email',
            'foo6' => 'email',
        );

        $vd = $v->validate($td, $rules, false);
        $this->assertNull($vd);
        $this->assertCount(6, $v->errors());
    }

    public function testValidate_Ip_Pass()
    {
        $v = new SimpleValidator();
        $td = array(
            'foo' => '127.0.0.1',
        );
        $rules = array(
            'foo' => 'ip',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($td, $vd);
    }

    public function testValidate_Ip_Failed()
    {
        $v = new SimpleValidator();
        $td = array(
            'foo1' => '127.0.0',
            'foo2' => '127.0.0.1.1',
            'foo3' => '127.0.0.256',
        );
        $rules = array(
            'foo1' => 'email',
            'foo2' => 'email',
            'foo3' => 'email',
        );

        $vd = $v->validate($td, $rules, false);
        $this->assertNull($vd);
        $this->assertCount(3, $v->errors());
    }

    public function testValidate_Url_Pass()
    {
        $v = new SimpleValidator();
        $td = array(
            'foo1' => 'http://test.com/1.html',
            'foo2' => 'https://test.com/1.html',
            'foo3' => 'ftp://username@test.com:21/1.html',
        );
        $rules = array(
            'foo1' => 'url',
            'foo2' => 'url',
            'foo3' => 'url',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($td, $vd);
    }

    public function testValidate_Url_Failed()
    {
        $v = new SimpleValidator();
        $td = array(
            'foo1' => 'test.com/1.html',
        );
        $rules = array(
            'foo1' => 'url',
        );

        $vd = $v->validate($td, $rules, false);
        $this->assertNull($vd);
        $this->assertCount(1, $v->errors());
    }

    public function testValidate_HttpUrl_Pass()
    {
        $v = new SimpleValidator();
        $td = array(
            'foo1' => 'http://test.com/1.html',
            'foo2' => 'https://test.com/1.html',
        );
        $rules = array(
            'foo1' => 'http_url',
            'foo2' => 'http_url',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($td, $vd);
    }

    public function testValidate_HttpUrl_Failed()
    {
        $v = new SimpleValidator();
        $td = array(
            'foo1' => 'ftp://username@test.com:21/1.html',
        );
        $rules = array(
            'foo1' => 'http_url',
        );

        $vd = $v->validate($td, $rules, false);
        $this->assertNull($vd);
        $this->assertCount(1, $v->errors());
    }

    public function testValidate_In_Pass()
    {
        $v = new SimpleValidator();
        $td = array(
            'foo1' => 'bar1',
            'foo2' => 'bar2',
        );
        $rules = array(
            'foo1' => 'in:bar1,bar2',
            'foo2' => 'in:bar2',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($td, $vd);
    }

    public function testValidate_In_Failed()
    {
        $v = new SimpleValidator();
        $td = array(
            'foo1' => 'bar0',
        );
        $rules = array(
            'foo1' => 'in:bar1,bar2',
        );

        $vd = $v->validate($td, $rules, false);
        $this->assertNull($vd);
        $this->assertCount(1, $v->errors());
    }

    public function testValidate_String_Pass()
    {
        $v = new SimpleValidator();

        $td = array(
            'foo1' => 'bar',
            'foo2' => 'bar2',
        );
        $rules = array(
            'foo1' => 'string',
        );

        $vd = $v->validate($td, $rules);

        $this->assertEquals($td['foo1'], $vd['foo1']);
        $this->assertFalse(isset($vd['foo2']));
    }

    public function testValidate_Date_Pass()
    {
        $v = new SimpleValidator();
        $td = array(
            'foo1' => '2017-01-01',
            'foo2' => new \DateTime(),
        );
        $rules = array(
            'foo1' => 'date',
            'foo2' => 'date',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($td, $vd);
    }

    public function testValidate_Date_Failed()
    {
        $v = new SimpleValidator();
        $td = array(
            'foo1' => 'aaaa-bb-cc',
            'foo2' => '2017-13-01',
            'foo3' => '2017-01-32',
        );
        $rules = array(
            'foo1' => 'date',
            'foo2' => 'date',
            'foo3' => 'date',
        );

        $vd = $v->validate($td, $rules, false);
        $this->assertNull($vd);
        $this->assertCount(count($td), $v->errors());
    }

    public function testValidate_DateAfter_Pass()
    {
        $v = new SimpleValidator();
        $td = array(
            'foo1' => '2017-01-02',
            'foo2' => '2017-02-01',
            'foo3' => '2018-01-01',
        );
        $rules = array(
            'foo1' => 'date_after:2017-01-01',
            'foo2' => 'date_after:2017-01-01',
            'foo3' => 'date_after:2017-01-01',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($td, $vd);
    }

    public function testValidate_DateAfter_Failed()
    {
        $v = new SimpleValidator();
        $td = array(
            'foo1' => '2017-06-06',
            'foo2' => '2017-06-05',
        );
        $rules = array(
            'foo1' => 'date_after:2017-06-06',
            'foo2' => 'date_after:2017-06-06',
        );

        $vd = $v->validate($td, $rules, false);
        $this->assertNull($vd);
        $this->assertCount(count($td), $v->errors());
    }

    public function testValidate_DateAfterOrEqual_Pass()
    {
        $v = new SimpleValidator();
        $td = array(
            'foo1' => '2017-01-01',
            'foo2' => '2017-01-02',
            'foo3' => '2017-02-01',
            'foo4' => '2018-01-01',
        );
        $rules = array(
            'foo1' => 'date_after_or_equal:2017-01-01',
            'foo2' => 'date_after_or_equal:2017-01-01',
            'foo3' => 'date_after_or_equal:2017-01-01',
            'foo4' => 'date_after_or_equal:2017-01-01',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($td, $vd);
    }

    public function testValidate_DateAfterOrEqual_Failed()
    {
        $v = new SimpleValidator();
        $td = array(
            'foo1' => '2017-06-05',
        );
        $rules = array(
            'foo1' => 'date_after_or_equal:2017-06-06',
        );

        $vd = $v->validate($td, $rules, false);
        $this->assertNull($vd);
        $this->assertCount(count($td), $v->errors());
    }

    public function testValidate_DateBefore_Pass()
    {
        $v = new SimpleValidator();
        $td = array(
            'foo1' => '2017-06-05',
            'foo2' => '2017-05-06',
            'foo3' => '2015-07-07',
        );
        $rules = array(
            'foo1' => 'date_before:2017-06-06',
            'foo2' => 'date_before:2017-06-06',
            'foo3' => 'date_before:2017-06-06',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($td, $vd);
    }

    public function testValidate_DateBefore_Failed()
    {
        $v = new SimpleValidator();
        $td = array(
            'foo1' => '2017-06-07',
            'foo2' => '2017-06-06',
        );
        $rules = array(
            'foo1' => 'date_before:2017-06-06',
            'foo2' => 'date_before:2017-06-06',
        );

        $vd = $v->validate($td, $rules, false);
        $this->assertNull($vd);
        $this->assertCount(count($td), $v->errors());
    }

    public function testValidate_DateBeforeOrEqual_Pass()
    {
        $v = new SimpleValidator();
        $td = array(
            'foo1' => '2017-06-06',
            'foo2' => '2017-06-05',
            'foo3' => '2017-05-05',
            'foo4' => '2016-06-06',
        );
        $rules = array(
            'foo1' => 'date_before_or_equal:2017-06-06',
            'foo2' => 'date_before_or_equal:2017-06-06',
            'foo3' => 'date_before_or_equal:2017-06-06',
            'foo4' => 'date_before_or_equal:2017-06-06',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($td, $vd);
    }

    public function testValidate_DateBeforeOrEqual_Failed()
    {
        $v = new SimpleValidator();
        $td = array(
            'foo1' => '2017-06-07',
        );
        $rules = array(
            'foo1' => 'date_before_or_equal:2017-06-06',
        );

        $vd = $v->validate($td, $rules, false);
        $this->assertNull($vd);
        $this->assertCount(count($td), $v->errors());
    }

    public function testRule()
    {
        $v = new SimpleValidator();
        $v->rule('chinese_alpha_num', function ($field, $value, $params) {
            return (bool) preg_match('/^[\x{4e00}-\x{9fa5}a-zA-z0-9]+$/u', $value);
        }, '{key} must be chinese.');

        $td = array(
            'foo1' => '你好HelloWorld',
        );
        $rules = array(
            'foo1' => 'chinese_alpha_num',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($td, $vd);
    }

    public function testExtend()
    {
        $v = new SimpleValidator();
        $v->extend(new CustomValidatorRule());

        $td = array(
            'foo1' => '你好HelloWorld',
        );
        $rules = array(
            'foo1' => 'chinese_alpha_num',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($td, $vd);
    }

    public function test_notRequired_emptyValue()
    {
        $v = new SimpleValidator();
        $td = array(
            'foo1' => '',
        );
        $rules = array(
            'foo1' => 'email',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($vd['foo1'], $td['foo1']);

        $v = new SimpleValidator();
        $td = array(
            'url' => null,
        );
        $rules = array(
            'url' => 'http_url',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($vd['url'], $td['url']);
        $this->assertTrue(is_null($vd['url']));

        $v = new SimpleValidator();
        $td = array(
            'num1' => 0,
        );
        $rules = array(
            'num1' => 'integer',
        );

        $vd = $v->validate($td, $rules);
        $this->assertEquals($vd['num1'], $td['num1']);
    }
}
