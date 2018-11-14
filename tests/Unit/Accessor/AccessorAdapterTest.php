<?php

namespace Tests\Unit\Accessor;

use Biz\Accessor\AccessorAdapter;
use Biz\BaseTestCase;
use Biz\Course\Accessor\JoinCourseAccessor;
use Biz\Course\Accessor\JoinCourseMemberAccessor;

class AccessorAdapterTest extends BaseTestCase
{
    public function testSetNextAccessor()
    {
        $accessor = new JoinCourseAccessor($this->biz);
        $accessor->setNextAccessor(new JoinCourseMemberAccessor($this->biz));

        $this->assertNotNull($accessor->getNextAccessor());
        $this->assertInstanceOf('Biz\Course\Accessor\JoinCourseMemberAccessor', $accessor->getNextAccessor());
    }

    public function testProcess()
    {
        $accessor = new JoinCourseAccessor($this->biz);
        $result = $accessor->process(null);
        $this->assertEquals('NOTFOUND_COURSE', $result['code']);
    }

    public function testProcessHasNextAccessor()
    {
        $accessor = new JoinCourseAccessor($this->biz);
        $accessor->setNextAccessor(new JoinCourseMemberAccessor($this->biz));
        $user = $this->getCurrentUser();
        $user['locked'] = 1;
        $result = $accessor->process(array('id' => 1, 'status' => 'draft'));
        $this->assertEquals('LOCKED_USER', $result['code']);
    }

    public function testHasError()
    {
        $accessor = new JoinCourseAccessor($this->biz);

        $result = $accessor->hasError(null, 'not.found');
        $this->assertFalse($result);

        $result = $accessor->hasError(array(AccessorAdapter::CONTEXT_ERROR_KEY => array('code' => 'not.found')), 'not.found');
        $this->assertTrue($result);
    }
}
