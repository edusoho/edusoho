<?php

namespace Biz\Testpaper\Tests;

use Biz\BaseTestCase;
use Biz\Testpaper\Copy\TestpapersCopy;
use AppBundle\Common\ReflectionUtils;

class TestpapersCopyTest extends BaseTestCase
{
    public function testDoCopy()
    {
        $testpapersCopy = new TestpapersCopy($this->getBiz(), 'test');
        $this->mockBiz(
            'Testpaper:TestpaperDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(),
                    'withParams' => array(
                        array(
                            'courseSetId' => 2,
                            'type' => 'testpaper',
                        ),
                        array(),
                        0,
                        PHP_INT_MAX
                    ),
                ),
            )
        );
        $result = $testpapersCopy->doCopy(array('id' => 2), array('newCourseSet' => array()));
        $this->assertNull($result);
    }

    public function testGetFields()
    {
        $testpapersCopy = new TestpapersCopy($this->getBiz(), 'test');
        $result = ReflectionUtils::invokeMethod($testpapersCopy, 'getFields');
        $this->assertEquals('name', $result[0]);
    }

    public function testCloneCourseSetTestpapers()
    {
        $testpapersCopy = new TestpapersCopy($this->getBiz(), 'test');
        $fields = array(array('id' => 2), array('newCourseSet' => array('id' => 2)));
        $this->mockBiz(
            'Testpaper:TestpaperDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(array('id' => 2)),
                    'withParams' => array(
                        array(
                            'courseSetId' => 2,
                            'type' => 'testpaper',
                        ),
                        array(),
                        0,
                        PHP_INT_MAX
                    ),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'batchCreate',
                    'withParams' => array(array(array(
                        'courseSetId' => 2,
                        'target' => 'course-2',
                        'createdUserId' => 1,
                        'updatedUserId' => 1,
                        'copyId' => 2
                    ))),
                ),
                array(
                    'functionName' => 'search',
                    'returnValue' => array(array('id' => 2, 'copyId' => 2)),
                    'withParams' => array(
                        array(
                            'courseSetId' => 2
                        ),
                        array(),
                        0,
                        PHP_INT_MAX
                    ),
                    'runTimes' => 1,
                ),
            )
        );
        $result = ReflectionUtils::invokeMethod($testpapersCopy, 'cloneCourseSetTestpapers', $fields);
        $this->assertNull($result);
    }

    public function testCloneTestpaperItems()
    {
        $testpapersCopy = new TestpapersCopy($this->getBiz(), 'test');
        $fields = array(array(array('id' => 2)), array(array('id' => 2, 'copyId' => 2)), array('id' => 2));
        $this->mockBiz(
            'Testpaper:TestpaperItemDao',
            array(
                array(
                    'functionName' => 'findItemsByTestIds',
                    'returnValue' => array(array('questionId' => 2, 'testId' => 2, 'seq' => 2, 'questionType' => 'type', 'parentId' => 2, 'score' => 5, 'missScore' => 5, 'type' => 'type')),
                    'withParams' => array(array(2)),
                ),
                array(
                    'functionName' => 'batchCreate',
                    'withParams' => array(array(array(
                        'testId' => 2,
                        'seq' => 2,
                        'questionId' => 2,
                        'questionType' => 'type',
                        'parentId' => 2,
                        'score' => 5,
                        'missScore' => 5,
                        'type' => 'type',
                    ))),
                ),
            )
        );
        $this->mockBiz(
            'Question:QuestionDao',
            array(
                array(
                    'functionName' => 'findQuestionsByCourseSetId',
                    'returnValue' => array(array('id' => 2, 'copyId' => 2)),
                    'withParams' => array(2),
                ),
            )
        );
        $result = ReflectionUtils::invokeMethod($testpapersCopy, 'cloneTestpaperItems', $fields);
        $this->assertNull($result);
    }
}