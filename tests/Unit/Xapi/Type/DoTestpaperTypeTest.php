<?php

namespace Tests\Unit\Xapi\Type;

use Biz\BaseTestCase;
use Biz\Xapi\Type\DoTestpaperType;

class DoTestpaperTypeTest extends BaseTestCase
{
    public function testPackages()
    {
        $type = new DoTestpaperType();
        $type->setBiz($this->biz);
        $packageInfo = $type->packages(array());

        $this->assertEmpty($packageInfo);

        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('storage', array()),
                    'returnValue' => array(
                        'cloud_access_key' => 'abc',
                        'cloud_secret_key' => 'efg',
                    ),
                ),
                array(
                    'functionName' => 'get',
                    'withParams' => array('site', array()),
                    'returnValue' => array(
                        'siteName' => 'abc',
                    ),
                ),
                array(
                    'functionName' => 'get',
                    'withParams' => array('xapi', array()),
                    'returnValue' => array(
                        'pushUrl' => '',
                    ),
                ),
            )
        );

        $testpaperService = $this->mockBiz('Testpaper:TestpaperService',
            array(
                array(
                    'functionName' => 'findTestpapersByIds',
                    'withParams' => array(array(1)),
                    'returnValue' => array(
                        0 => array(
                            'id' => 1,
                            'score' => 12,
                        ),
                    ),
                ),
                array(
                    'functionName' => 'findTestpaperResultsByIds',
                    'withParams' => array(array(1)),
                    'returnValue' => array(
                        0 => array(
                            'id' => 1,
                            'testId' => 1,
                            'courseId' => 2,
                            'courseSetId' => 3,
                            'passedStatus' => 'passed',
                            'score' => 10,
                            'paperName' => 'test paper',
                        ),
                    ),
                ),
            )
        );

        $courseService = $this->mockBiz(
            'Course:CourseDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(
                        0 => array(
                            'id' => 2,
                            'title' => 'course title',
                            'courseSetId' => 3,
                        ),
                    ),
                ),
            )
        );

        $courseSetService = $this->mockBiz(
            'Course:CourseSetDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(
                        0 => array(
                            'id' => 3,
                            'title' => 'course set title',
                            'subtitle' => 'course set subtitle',
                        ),
                    ),
                ),
            )
        );

        $type = new DoTestpaperType();
        $type->setBiz($this->biz);
        $packageInfo = $type->packages(array(
            array(
                'target_id' => 1,
                'user_id' => 12121,
                'uuid' => '123123123dse',
                'occur_time' => time(),
            ),
        ));

        $testpaperService->shouldHaveReceived('findTestpaperResultsByIds');
        $testpaperService->shouldHaveReceived('findTestpapersByIds');
        $courseService->shouldHaveReceived('search');
        $courseSetService->shouldHaveReceived('search');

        $packageInfo = reset($packageInfo);

        $this->assertEquals('123123123dse', $packageInfo['id']);
        $this->assertEquals('http://adlnet.gov/expapi/verbs/completed', $packageInfo['verb']['id']);
        $this->assertEquals(1, $packageInfo['object']['id']);
        $this->assertArrayEquals(
            array('title' => 'course set title-course title', 'description' => 'course set subtitle'),
            $packageInfo['object']['definition']['extensions']['http://xapi.edusoho.com/extensions/course']
        );
    }
}
