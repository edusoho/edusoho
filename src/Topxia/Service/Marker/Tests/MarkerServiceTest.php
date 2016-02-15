<?php

namespace Topxia\Service\Marker\Tests;

use Topxia\Service\Common\BaseTestCase;

class MarkerServiceTest extends BaseTestCase
{
    public function testAddMarker()
    {
        $this->getCourseService()->createCourse(array(
            'title' => 'testCourse'
        ));
        $fields = array(
            'second'     => 30,
            'questionId' => 1
        );
        $arguments = array(
            'type'     => 'single_choice',
            'parentId' => 0,
            'stem'     => '111',
            'answer'   => array(1),
            'choices'  => array(1, 2, 3, 4),
            'target'   => "course-1"
        );

        $this->getQuestionService()->createQuestion($arguments);
        $marker = $this->getMarkerService()->addMarker(1, $fields);
        $this->assertEquals($marker['markerId'], 1);
        $this->assertEquals($marker['questionId'], 1);
        return $marker;
    }

    public function testGetMarker()
    {
        $this->getCourseService()->createCourse(array(
            'title' => 'testCourse'
        ));
        $fields = array(
            'second'     => 30,
            'questionId' => 1
        );
        $arguments = array(
            'type'     => 'single_choice',
            'parentId' => 0,
            'stem'     => '111',
            'answer'   => array(1),
            'choices'  => array(1, 2, 3, 4),
            'target'   => "course-1"
        );

        $this->getQuestionService()->createQuestion($arguments);
        $marker = $this->getMarkerService()->addMarker(1, $fields);
        $marker = $this->getMarkerService()->getMarker($marker['id']);
        $this->assertEquals($marker['mediaId'], 0);
        $this->assertEquals($marker['second'], 30);
        return $marker;
    }

    public function testGetMarkersByIds()
    {
        $this->getCourseService()->createCourse(array(
            'title' => 'testCourse'
        ));
        $fields = array(
            'second'     => 30,
            'questionId' => 1
        );
        $arguments = array(
            'type'     => 'single_choice',
            'parentId' => 0,
            'stem'     => '111',
            'answer'   => array(1),
            'choices'  => array(1, 2, 3, 4),
            'target'   => "course-1"
        );

        $this->getQuestionService()->createQuestion($arguments);
        $marker1 = $this->getMarkerService()->addMarker(1, $fields);
        $marker2 = $this->getMarkerService()->addMarker(3, $fields);
        $markers = $this->getMarkerService()->getMarkersByIds(array(1, 2));
        $this->assertEquals($markers[1]['mediaId'], 0);
        $this->assertEquals($markers[2]['mediaId'], 0);
        return $markers;
    }

    public function testSearchMarkers()
    {
        $this->getCourseService()->createCourse(array(
            'title' => 'testCourse'
        ));
        $fields = array(
            'second'     => 30,
            'questionId' => 1
        );
        $arguments = array(
            'type'     => 'single_choice',
            'parentId' => 0,
            'stem'     => '111',
            'answer'   => array(1),
            'choices'  => array(1, 2, 3, 4),
            'target'   => "course-1"
        );

        $this->getQuestionService()->createQuestion($arguments);
        $marker1    = $this->getMarkerService()->addMarker(1, $fields);
        $marker2    = $this->getMarkerService()->addMarker(3, $fields);
        $marker3    = $this->getMarkerService()->addMarker(3, $fields);
        $conditions = array(
            'mediaId' => 0
        );
        $markers = $this->getMarkerService()->searchMarkers($conditions, array('createdTime', 'DESC'), 0, 10);
        $this->assertEquals($markers[0]['mediaId'], 0);
        return $markers;
    }

    public function testUpdateMarker()
    {
        $this->getCourseService()->createCourse(array(
            'title' => 'testCourse'
        ));
        $fields = array(
            'second'     => 30,
            'questionId' => 1
        );
        $arguments = array(
            'type'     => 'single_choice',
            'parentId' => 0,
            'stem'     => '111',
            'answer'   => array(1),
            'choices'  => array(1, 2, 3, 4),
            'target'   => "course-1"
        );

        $this->getQuestionService()->createQuestion($arguments);
        $marker1 = $this->getMarkerService()->addMarker(1, $fields);
        $marker1 = $this->getMarkerService()->getMarker($marker1['markerId']);

        $this->assertEquals($marker1['second'], 30);
        $fields = array(
            'second'      => 20,
            'updatedTime' => time()
        );
        $marker2 = $this->getMarkerService()->updateMarker($marker1['id'], $fields);
        $this->assertEquals($marker2['second'], 20);
        return $marker2;

    }

    public function testDeleteMarker()
    {
        $this->getCourseService()->createCourse(array(
            'title' => 'testCourse'
        ));
        $fields = array(
            'second'     => 30,
            'questionId' => 1
        );
        $arguments = array(
            'type'     => 'single_choice',
            'parentId' => 0,
            'stem'     => '111',
            'answer'   => array(1),
            'choices'  => array(1, 2, 3, 4),
            'target'   => "course-1"
        );

        $this->getQuestionService()->createQuestion($arguments);
        $marker1 = $this->getMarkerService()->addMarker(1, $fields);
        $marker1 = $this->getMarkerService()->getMarker($marker1['markerId']);
        $this->assertEquals($marker1['second'], 30);
        $marker = $this->getMarkerService()->deleteMarker($marker1['id']);
        $this->assertEquals($marker, true);
        return $marker;
    }

    protected function getMarkerService()
    {
        return $this->getServiceKernel()->createService('Marker.MarkerService');
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    protected function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}
