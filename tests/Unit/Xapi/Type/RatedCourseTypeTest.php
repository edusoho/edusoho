<?php

namespace Tests\Unit\Xapi\Type;

use Biz\BaseTestCase;
use Biz\Xapi\Type\RatedClassroomType;

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
                        0 => array(
                            'id' => 2,
                            'title' => 'course title',
                            'courseSetId' => 3,
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
                        0 => array(
                            'id' => 3,
                            'title' => 'course set title',
                            'subtitle' => 'course set subtitle',
                        ),
                    ),
                ),
            )
        );

        $courseDao->shouldHaveReceived('search');
        $courseSetDao->shouldHaveReceived('search');

        $type = new RatedClassroomType();
        $type->setBiz($this->biz);

        $statements = array(
            array('user_id' => 1, 'uuid' => 10, 'target_id' => 1, 'target_type' => 'course', 'occur_time' => time(), 'context' => array('score' => array('raw' => 3, 'max' => 5, 'min' => 0))),
        );
        $pushStatements = $type->packages($statements);

        $this->assertEquals(array('id', 'actor', 'verb', 'timestamp', 'object', 'result'), array_keys($pushStatements[0]));
        $this->assertEquals(array('id' => 'http://id.tincanapi.com/verb/rated', 'display' => array(
            'zh-CN' => '评分了', 'en-US' => 'rated'
        )), $pushStatements[0]['verb']);

        $this->assertEquals(array('score' => array('raw' => 3, 'max' => 5, 'min' => 0)), $pushStatements[0]['result']);
    }

    /**
     * @return \Biz\System\Service\SettingService
     */
    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
