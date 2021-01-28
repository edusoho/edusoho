<?php

namespace Tests\Unit\Xapi\Type;

use Biz\BaseTestCase;
use Biz\Xapi\Type\BookmarkedCourseType;

class BookmarkedCourseTypeTest extends BaseTestCase
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
                            'courseSetId' => 1,
                            'title' => 'course title',
                            'price' => 199,
                        ),
                        2 => array(
                            'id' => 2,
                            'courseSetId' => 1,
                            'title' => 'course title',
                            'price' => 199,
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
                            'tags' => array(1, 2),
                        ),
                        2 => array(
                            'id' => 2,
                            'title' => 'course set title',
                            'subtitle' => 'course set subtitle',
                            'tags' => array(1, 2),
                        ),
                    ),
                ),
            )
        );

        $tagService = $this->mockBiz(
            'Taxonomy:TagService',
            array(
                array(
                    'functionName' => 'findTagsByIds',
                    'returnValue' => array(
                        1 => array(
                            'id' => 1,
                            'name' => 'java',
                        ),
                        2 => array(
                            'id' => 2,
                            'name' => 'php',
                        ),
                    ),
                ),
            )
        );

        $type = new BookmarkedCourseType();
        $type->setBiz($this->biz);

        $statements = array(
            array('user_id' => 1, 'uuid' => 10, 'target_id' => 1, 'target_type' => 'course', 'occur_time' => time(),
                'context' => array('course' => array('id' => 1, 'tags' => '|数据结构|', 'price' => 99, 'title' => '数据结构(上)(自主模式)', 'description' => 'aaa')), ),
            array('user_id' => 2, 'uuid' => 20, 'target_id' => 2, 'target_type' => 'course', 'occur_time' => time(),
                'context' => array('course' => array('id' => 2, 'tags' => '|数据结构|', 'price' => 299, 'title' => '数据结构(下)(自主模式)', 'description' => 'aaa')), ),
        );
        $pushStatements = $type->packages($statements);

        $this->assertEquals(array('id', 'actor', 'verb', 'object', 'timestamp'), array_keys($pushStatements[0]));
        foreach ($pushStatements as $index => $st) {
            $this->assertEquals(array('id' => 'https://w3id.org/xapi/adb/verbs/bookmarked', 'display' => array(
                'zh-CN' => '收藏了', 'en-US' => 'bookmarked',
            )), $st['verb']);
            $this->assertNotNull($st['object']['definition']['extensions']);
        }
    }

    /**
     * @return \Biz\System\Service\SettingService
     */
    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
