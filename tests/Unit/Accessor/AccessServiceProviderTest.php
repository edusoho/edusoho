<?php

namespace Tests\Unit\Accessor;

use Biz\BaseTestCase;

class AccessServiceProviderTest extends BaseTestCase
{
    public function testRegister()
    {
        $this->assertNotNull($this->biz['course.join_chain']);
        $this->assertNotNull($this->biz['course.learn_chain']);
        $this->assertNotNull($this->biz['classroom.join_chain']);
        $this->assertNotNull($this->biz['classroom.learn_chain']);
        $this->assertNotNull($this->biz['course.task.learn_chain']);
    }
}
