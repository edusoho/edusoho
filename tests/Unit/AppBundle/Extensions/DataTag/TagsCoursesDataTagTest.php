<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\TagsCoursesDataTag;

class TagsCoursesDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCheckCountEmpty()
    {
        $datatag = new TagsCoursesDataTag();
        $datatag->getData(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCheckCountError()
    {
        $datatag = new TagsCoursesDataTag();
        $datatag->getData(array('count' => 200));
    }

    public function testGetDataEmpty()
    {
        $datatag = new TagsCoursesDataTag();
        $result = $datatag->getData(array('count' => 5, 'tags' => array('name1', 'name2')));
        $this->assertEmpty($result);

        $this->mockBiz('Taxonomy:TagService', array(
            array(
                'functionName' => 'findTagsByNames',
                'returnValue' => array(array('id' => 1), array('id' => 2)),
            ),
            array(
                'functionName' => 'findTagOwnerRelationsByTagIdsAndOwnerType',
                'returnValue' => array(),
            ),
        ));

        $datatag = new TagsCoursesDataTag();
        $result = $datatag->getData(array('count' => 5, 'tags' => array('name1', 'name2')));
        $this->assertEmpty($result);
    }

    public function testGetDataTagOwnerEmpty()
    {
        $this->mockBiz('Taxonomy:TagService', array(
            array(
                'functionName' => 'findTagsByNames',
                'returnValue' => array(array('id' => 1), array('id' => 2)),
            ),
            array(
                'functionName' => 'findTagOwnerRelationsByTagIdsAndOwnerType',
                'returnValue' => array(array('id' => 1, 'ownerId' => 1), array('id' => 2, 'ownerId' => 2)),
            ),
        ));

        $datatag = new TagsCoursesDataTag();
        $result = $datatag->getData(array('count' => 5, 'tags' => array('name1', 'name2')));
        $this->assertEmpty($result);
    }

    public function testGetData()
    {
        $this->mockBiz('Taxonomy:TagService', array(
            array(
                'functionName' => 'findTagsByNames',
                'returnValue' => array(array('id' => 1), array('id' => 2)),
            ),
            array(
                'functionName' => 'findTagOwnerRelationsByTagIdsAndOwnerType',
                'returnValue' => array(array('id' => 1, 'ownerId' => 1), array('id' => 1, 'ownerId' => 2), array('id' => 2, 'ownerId' => 2)),
            ),
        ));

        $this->mockBiz('Course:CourseSetService', array(
            array(
                'functionName' => 'searchCourseSets',
                'returnValue' => array(array('id' => 1)),
            ),
        ));

        $datatag = new TagsCoursesDataTag();
        $results = $datatag->getData(array('count' => 5, 'tags' => array('name1', 'name2')));
        $this->assertNotNull($results);
        $this->assertEquals(1, count($results));
    }
}
