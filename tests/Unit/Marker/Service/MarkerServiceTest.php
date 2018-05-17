<?php

namespace Tests\Unit\Marker\Service;

use Biz\BaseTestCase;

class MarkerServiceTest extends BaseTestCase
{
    public function testAddMarker()
    {
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );
        $arguments = array(
            'type' => 'single_choice',
            'parentId' => 0,
            'stem' => '111',
            'answer' => array(1),
            'choices' => array(1, 2, 3, 4),
            'target' => 'course-1',
            'courseSetId' => 1,
        );

        $this->getQuestionService()->create($arguments);
        $marker = $this->getMarkerService()->addMarker(1, $fields);
        $this->assertEquals($marker['markerId'], 1);
        $this->assertEquals($marker['questionId'], 1);

        return $marker;
    }

    public function testGetMarker()
    {
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );
        $arguments = array(
            'type' => 'single_choice',
            'parentId' => 0,
            'stem' => '111',
            'answer' => array(1),
            'choices' => array(1, 2, 3, 4),
            'target' => 'course-1',
            'courseSetId' => 1,
        );

        $this->getQuestionService()->create($arguments);
        $marker = $this->getMarkerService()->addMarker(1, $fields);
        $marker = $this->getMarkerService()->getMarker($marker['id']);
        $this->assertEquals($marker['mediaId'], 0);
        $this->assertEquals($marker['second'], 30);

        return $marker;
    }

    public function testGetMarkersByIds()
    {
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );
        $arguments = array(
            'type' => 'single_choice',
            'parentId' => 0,
            'stem' => '111',
            'answer' => array(1),
            'choices' => array(1, 2, 3, 4),
            'target' => 'course-1',
            'courseSetId' => 1,
        );

        $this->getQuestionService()->create($arguments);
        $this->getMarkerService()->addMarker(1, $fields);
        $this->getMarkerService()->addMarker(3, $fields);
        $markers = $this->getMarkerService()->getMarkersByIds(array(1, 2));
        $this->assertEquals($markers[1]['mediaId'], 0);
        $this->assertEquals($markers[2]['mediaId'], 0);

        return $markers;
    }

    public function testSearchMarkers()
    {
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );
        $arguments = array(
            'type' => 'single_choice',
            'parentId' => 0,
            'stem' => '111',
            'answer' => array(1),
            'choices' => array(1, 2, 3, 4),
            'target' => 'course-1',
            'courseSetId' => 1,
        );

        $this->getQuestionService()->create($arguments);
        $this->getMarkerService()->addMarker(1, $fields);
        $this->getMarkerService()->addMarker(3, $fields);
        $this->getMarkerService()->addMarker(3, $fields);
        $conditions = array(
            'mediaId' => 0,
        );
        $markers = $this->getMarkerService()->searchMarkers($conditions, array('createdTime' => 'DESC'), 0, 10);
        $this->assertEquals($markers[0]['mediaId'], 0);

        return $markers;
    }

    public function testUpdateMarker()
    {
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );
        $arguments = array(
            'type' => 'single_choice',
            'parentId' => 0,
            'stem' => '111',
            'answer' => array(1),
            'choices' => array(1, 2, 3, 4),
            'target' => 'course-1',
            'courseSetId' => 1,
        );

        $this->getQuestionService()->create($arguments);
        $marker1 = $this->getMarkerService()->addMarker(1, $fields);
        $marker1 = $this->getMarkerService()->getMarker($marker1['markerId']);

        $this->assertEquals($marker1['second'], 30);
        $fields = array(
            'second' => 20,
            'updatedTime' => time(),
        );
        $marker2 = $this->getMarkerService()->updateMarker($marker1['id'], $fields);
        $this->assertEquals($marker2['second'], 20);

        return $marker2;
    }

    public function testDeleteMarker()
    {
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );
        $arguments = array(
            'type' => 'single_choice',
            'parentId' => 0,
            'stem' => '111',
            'answer' => array(1),
            'choices' => array(1, 2, 3, 4),
            'target' => 'course-1',
            'courseSetId' => 1,
        );

        $this->getQuestionService()->create($arguments);
        $marker1 = $this->getMarkerService()->addMarker(1, $fields);
        $marker1 = $this->getMarkerService()->getMarker($marker1['markerId']);
        $this->assertEquals($marker1['second'], 30);
        $marker = $this->getMarkerService()->deleteMarker($marker1['id']);
        $this->assertEquals($marker, true);

        return $marker;
    }

    private function createCourse($customFields = array())
    {
        $defaultFields = array(
            'title' => 'test-create-course',
            'courseSetId' => 1,
            'learnMode' => 'freeMode',
            'expiryMode' => 'forever',
            'expiryStartDate' => '',
            'expiryEndDate' => '',
        );

        $fields = array_merge($defaultFields, $customFields);

        return $this->getCourseService()->createCourse($fields);
    }

    protected function getMarkerService()
    {
        return $this->createService('Marker:MarkerService');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
