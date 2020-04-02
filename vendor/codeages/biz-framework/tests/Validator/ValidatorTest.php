<?php

namespace Tests\Validator;

use Codeages\Biz\Framework\Validator\Validator;
use Codeages\Biz\Framework\Validator\ValidatorException;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function testValidate_whenValidData_thenPass()
    {
        $v = new Validator();

        $data = array (
            'username' => 'guest',
            'email' => 'guest@example.com',
            'age' => 18,
        );

        $rules = array (
            'username' => array('required', array('lengthBetween', 4, 16)),
            'email' => array('required', 'email'),
            'age' => array('required', 'integer', array('min', 18), array('max', 100)),
        );

        $rules = array(
            'username' => array('required', array('lengthBetween', 4, 16)),
            'email' => array('required', 'email'),
            'age' => array('required', 'integer', array('min', 18), array('max', 100)),
        );

        $validatedData = $v->validate($data, $rules);

        $this->assertEquals($data['username'], $validatedData['username']);
        $this->assertEquals($data['email'], $validatedData['email']);
        $this->assertEquals($data['age'], $validatedData['age']);
    }

    public function testValidate_whenInvalidData_thenThrowException()
    {
        $this->expectException('Codeages\Biz\Framework\Validator\ValidatorException');

        $v = new Validator();

        $data = array(
            'username' => 'guest',
            'email' => 'guest@example.com',
            'age' => 12,
        );

        $rules = array(
            'username' => array('required', array('lengthBetween', 4, 16)),
            'email' => array('required', 'email'),
            'age' => array('required', 'integer', array('min', 18), array('max', 100)),
        );

        $v->validate($data, $rules);
    }

    public function testValidate_whenLessRules_thenFilterData()
    {
        $v = new Validator();

        $data = array(
            'username' => 'guest',
            'email' => 'guest@example.com',
            'age' => 18,
        );

        $rules = array(
            'username' => array('required', array('lengthBetween', 4, 16)),
            'email' => array('required', 'email'),
        );

        $validatedData = $v->validate($data, $rules);

        $this->assertEquals($data['username'], $validatedData['username']);
        $this->assertEquals($data['email'], $validatedData['email']);
        $this->assertNotContains('age', $validatedData);
    }
}
