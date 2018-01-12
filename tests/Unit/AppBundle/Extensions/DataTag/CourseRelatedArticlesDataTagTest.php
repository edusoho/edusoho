<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\CourseRelatedArticlesDataTag;

class CourseRelatedArticlesDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testArgumentError()
    {
        $dataTag = new CourseRelatedArticlesDataTag();
        $dataTag->getData(array());
    }

    public function testGetData()
    {
        $datatag = new CourseRelatedArticlesDataTag();

        $articles = $datatag->getData(array('courseId' => 1));
        $this->assertEmpty($articles);

        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getCourse',
                'returnValue' => array('id' => 1, 'title' => 'course title'),
            ),
        ));

        $this->mockBiz('Taxonomy:TagService', array(
            array(
                'functionName' => 'findTagsByOwner',
                'returnValue' => array(array('id' => 1, 'name' => 'tag name1'), array('id' => 2, 'name' => 'tag name2')),
            ),
        ));

        $this->mockBiz('Article:ArticleService', array(
            array(
                'functionName' => 'findPublishedArticlesByTagIdsAndCount',
                'returnValue' => array(array('id' => 1, 'title' => 'article title1', 'status' => 'published'), array('id' => 2, 'title' => 'article title2', 'status' => 'published')),
            ),
        ));

        $articles = $datatag->getData(array('courseId' => 1, 'count' => 3));
        $this->assertEquals(2, count($articles));
        $this->assertEquals('published', $articles[0]['status']);
    }
}
