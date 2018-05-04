<?php

namespace Tests\Unit\Xapi\Type;

use Biz\BaseTestCase;
use Biz\Xapi\Type\RatedCourseType;

class RatedCourseTypeTest extends BaseTestCase
{
    public function testPackages()
    {
        $this->getSettingService()->set('storage', array(
            'cloud_access_key' => 1,
            'cloud_secret_key' => 2,
        ));


        $courseDao = $this->mockBiz(
            'Course:CourseDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(
                        1 => array(
                            'id' => 1,
                            'title' => 'course title',
                            'courseSetId' => 1,
                        ),
                    ),
                ),
            )
        );

        $courseSetDao = $this->mockBiz(
            'Course:CourseSetDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(
                        1 => array(
                            'id' => 1,
                            'title' => 'course set title',
                            'subtitle' => 'course set subtitle',
                        ),
                    ),
                ),
            )
        );

        $type = new RatedCourseType();
        $type->setBiz($this->biz);

        $statements = array(
            array('user_id' => 1, 'uuid' => 10, 'target_id' => 1, 'target_type' => 'course', 'occur_time' => time(), 'context' => array('score' => array('raw' => 3, 'max' => 5, 'min' => 1), 'response' => '这门课程很好')),
        );
        $pushStatements = $type->packages($statements);

        $courseDao->shouldHaveReceived('search');
        $courseSetDao->shouldHaveReceived('search');

        $this->assertEquals(array('id', 'actor', 'verb', 'object', 'result', 'timestamp'), array_keys($pushStatements[0]));
        $this->assertEquals(array('id' => 'http://id.tincanapi.com/verb/rated', 'display' => array(
            'zh-CN' => '评分了', 'en-US' => 'rated'
        )), $pushStatements[0]['verb']);

        $this->assertEquals(1, $pushStatements[0]['object']['id']);
        $this->assertEquals(array('raw' => 3, 'max' => 5, 'min' => 1), $pushStatements[0]['result']['score']);
        $this->assertEquals('这门课程很好', $pushStatements[0]['result']['response']);
    }

    /**
     * @return \Biz\System\Service\SettingService
     */
    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
