<?php

namespace Tests\Unit\Testpaper\Event;

use Biz\BaseTestCase;
use Biz\Testpaper\Event\TestpaperSyncSubscriber;
use Codeages\Biz\Framework\Event\Event;

class TestpaperSyncSubscriberTest extends BaseTestCase
{
    public function testOnTestpaperUpdate()
    {
        $subscriber = new TestpaperSyncSubscriber($this->biz);
        $event = new Event(
            array(
                'copyId' => 0,
                'type' => 'test',
                'id' => 1,
                'courseSetId' => 10,
            )
        );

        $this->mockBiz('Course:CourseSetDao', array(
            array('functionName' => 'findCourseSetsByParentIdAndLocked', 'returnValue' => array(array('id' => 1))),
        ));
        $mockTestpaperDao = $this->mockBiz('Testpaper:TestpaperDao', array(
            array('functionName' => 'findTestpapersByCopyIdAndCourseSetIds', 'returnValue' => array(array('id' => 1))),
            array('functionName' => 'update', 'returnValue' => array(array('id' => 1))),
        ));

        $subscriber->onTestpaperUpdate($event);
        $mockTestpaperDao->shouldHaveReceived('update');
    }

    public function testOnTestpaperItemCreate()
    {
        $subscriber = new TestpaperSyncSubscriber($this->biz);

        $event = new Event(
            array(
                'copyId' => 1,
            )
        );
        $result = $subscriber->onTestpaperItemCreate($event);
        $this->assertNull($result);

        $event = new Event(
            array(
                'copyId' => 0,
                'courseId' => 1,
            )
        );
        $result = $subscriber->onTestpaperItemCreate($event);
        $this->assertNull($result);

        $event = new Event(
            array(
                'copyId' => 0,
                'courseId' => 1,
                'seq' => 1,
                'questionId' => 12,
                'questionType' => 'singe',
                'parentId' => 14,
                'score' => 2,
                'missScore' => 0,
                'id' => 2,
            )
        );
        $this->mockBiz('Course:CourseDao', array(
            array('functionName' => 'findCoursesByParentIdAndLocked', 'returnValue' => array(array('courseSetId' => 1))),
        ));
        $this->mockBiz('Testpaper:TestpaperDao', array(
            array('functionName' => 'findTestpapersByCopyIdAndCourseSetIds', 'returnValue' => array(array('id' => 1))),
        ));
        $mockTestpaperItemDao = $this->mockBiz('Testpaper:TestpaperItemDao', array(
            array('functionName' => 'create', 'returnValue' => array()),
        ));

        $subscriber->onTestpaperItemCreate($event);
        $mockTestpaperItemDao->shouldHaveReceived('create');
    }

    public function testOnTestpaperItemUpdate()
    {
        $subscriber = new TestpaperSyncSubscriber($this->biz);

        $event = new Event(
            array(
                'copyId' => 1,
            )
        );
        $result = $subscriber->onTestpaperItemUpdate($event);
        $this->assertNull($result);

        $event = new Event(
            array(
                'copyId' => 0,
                'courseId' => 1,
            )
        );
        $result = $subscriber->onTestpaperItemUpdate($event);
        $this->assertNull($result);

        $event = new Event(
            array(
                'copyId' => 0,
                'courseId' => 1,
                'seq' => 1,
                'score' => 2,
                'missScore' => 0,
                'id' => 2,
            )
        );
        $this->mockBiz('Course:CourseDao', array(
            array('functionName' => 'findCoursesByParentIdAndLocked', 'returnValue' => array(array('courseSetId' => 1))),
        ));
        $this->mockBiz('Testpaper:TestpaperDao', array(
            array('functionName' => 'findTestpapersByCopyIdAndCourseSetIds', 'returnValue' => array(array('id' => 1))),
        ));
        $mockTestpaperItemDao = $this->mockBiz('Testpaper:TestpaperItemDao', array(
            array('functionName' => 'findTestpaperItemsByCopyIdAndLockedTestIds', 'returnValue' => array(array('id' => 1))),
            array('functionName' => 'update', 'returnValue' => array()),
        ));

        $subscriber->onTestpaperItemUpdate($event);
        $mockTestpaperItemDao->shouldHaveReceived('update');
    }

    public function testOnTestpaperItemDelete()
    {
        $subscriber = new TestpaperSyncSubscriber($this->biz);

        $event = new Event(
            array(
                'copyId' => 1,
            )
        );
        $result = $subscriber->onTestpaperItemDelete($event);
        $this->assertNull($result);

        $event = new Event(
            array(
                'copyId' => 0,
                'courseId' => 1,
            )
        );
        $result = $subscriber->onTestpaperItemDelete($event);
        $this->assertNull($result);

        $event = new Event(
            array(
                'copyId' => 0,
                'courseId' => 1,
                'id' => 2,
            )
        );
        $this->mockBiz('Course:CourseDao', array(
            array('functionName' => 'findCoursesByParentIdAndLocked', 'returnValue' => array(array('courseSetId' => 1))),
        ));
        $this->mockBiz('Testpaper:TestpaperDao', array(
            array('functionName' => 'findTestpapersByCopyIdAndCourseSetIds', 'returnValue' => array(array('id' => 1))),
        ));
        $mockTestpaperItemDao = $this->mockBiz('Testpaper:TestpaperItemDao', array(
            array('functionName' => 'findTestpaperItemsByCopyIdAndLockedTestIds', 'returnValue' => array(array('id' => 1))),
            array('functionName' => 'delete', 'returnValue' => array()),
        ));

        $subscriber->onTestpaperItemDelete($event);
        $mockTestpaperItemDao->shouldHaveReceived('delete');
    }
}
