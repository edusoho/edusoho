<?php

namespace Tests\Validation;

use Codeages\Biz\Framework\Validation\RuleParser;
use PHPUnit\Framework\TestCase;

class RuleParserTest extends TestCase
{
    public function testParse()
    {
        $rules = RuleParser::parse('required|between:10,20');

        $this->assertEquals('required', $rules[0]);
        $this->assertEquals('between', $rules[1][0]);
        $this->assertEquals(10, $rules[1][1]);
        $this->assertEquals(20, $rules[1][2]);
    }
}
