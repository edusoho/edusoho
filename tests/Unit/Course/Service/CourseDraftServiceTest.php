<?php

namespace Tests\Unit\Course\Service;

use Biz\BaseTestCase;
use Biz\Course\Service\CourseDraftService;

class CourseDraftServiceTest extends BaseTestCase
{
    public function testCreateCourseDraft()
    {
        $draft = array(
            'title' => 'testTitle',
            'courseId' => 1,
            'summary' => 'xxxxx',
            'content' => 'cxcxcx',
            'activityId' => 2,
            );

        $draft1 = $this->getCourseDraftService()->createCourseDraft($draft);
        $this->assertEquals($draft['title'], $draft1['title']);
    }

    public function testUpdateCourseDraft()
    {
        $draft = array(
            'title' => 'testTitle',
            'courseId' => 1,
            'summary' => 'xxxxx',
            'content' => 'cxcxcx',
            'activityId' => 2,
        );

        $draft1 = $this->getCourseDraftService()->createCourseDraft($draft);
        $draft2 = $this->getCourseDraftService()->updateCourseDraft($draft1['id'], array('title' => 'testTitle1'));
        $this->assertEquals('testTitle1', $draft2['title']);
    }

    /**
     * @expectedException  \Biz\Course\CourseDraftException
     */
    public function testUpdateCourseDraftWithException()
    {
        $draft2 = $this->getCourseDraftService()->updateCourseDraft(3, array('title' => 'testTitle1'));
    }

    public function testDeleteCourseDrafts()
    {
        $draft = array(
            'title' => 'testTitle',
            'courseId' => 1,
            'summary' => 'xxxxx',
            'content' => 'cxcxcx',
            'activityId' => 2,
        );
        $draft1 = $this->getCourseDraftService()->createCourseDraft($draft);
        $this->getCourseDraftService()->deleteCourseDrafts(1, 2, $this->getCurrentUser()->getId());
        $draft2 = $this->getCourseDraftService()->getCourseDraft($draft1['id']);
        $this->assertEmpty($draft2);
    }

    public function testGetCourseDraftByCourseIdAndActivityIdAndUserId()
    {
        $draft = array(
            'title' => 'testTitle',
            'courseId' => 1,
            'summary' => 'xxxxx',
            'content' => 'cxcxcx',
            'activityId' => 2,
        );

        $draft1 = $this->getCourseDraftService()->createCourseDraft($draft);

        $draft2 = $this->getCourseDraftService()->getCourseDraftByCourseIdAndActivityIdAndUserId(1, 2, $this->getCurrentUser()->getId());
        $draft3 = $this->getCourseDraftService()->getCourseDraftByCourseIdAndActivityIdAndUserId(5, 2, $this->getCurrentUser()->getId());

        $this->assertEquals($draft1['title'], $draft2['title']);
        $this->assertEmpty($draft3);
    }

    public function testGetCourseDraft()
    {
        $draft = array(
            'title' => 'testTitle',
            'courseId' => 1,
            'summary' => 'xxxxx',
            'content' => 'cxcxcx',
            'activityId' => 2,
        );

        $draft1 = $this->getCourseDraftService()->createCourseDraft($draft);
        $draft2 = $this->getCourseDraftService()->getCourseDraft($draft1['id']);
        $this->assertEquals('testTitle', $draft2['title']);
    }

    /**
     * @return CourseDraftService
     */
    public function getCourseDraftService()
    {
        return $this->createService('Course:CourseDraftService');
    }
}
