<?php

namespace Topxia\Service\Marker\Tests;

use Topxia\Service\Common\BaseTestCase;

class QuestionMarkerServiceTest extends BaseTestCase
{
    public function testGetQuestionMarker()
    {
        $questionMarker = array(
            'markerId'   => 1,
            'questionId' => 1,
            'type'       => 'Math'
        );

        $questionMarker = $this->getQuestionMarkerService()->addQuestionMarker($questionMarker);

        $result = $this->getQuestionMarkerService()->getQuestionMarker($questionMarker['id']);
        $this->assertEquals('Math', $result['type']);
        $this->assertEquals(1, $result['markerId']);
        $this->assertEquals(1, $result['questionId']);

    }

    public function testFindQuestionMarkersByIds()
    {
        $questionMarker1 = array(
            'markerId'   => 1,
            'questionId' => 1,
            'type'       => 'Math'
        );

        $questionMarker2 = array(
            'markerId'   => 1,
            'questionId' => 2,
            'type'       => 'Math'
        );

        $questionMarker1 = $this->getQuestionMarkerService()->addQuestionMarker($questionMarker1);
        $questionMarker2 = $this->getQuestionMarkerService()->addQuestionMarker($questionMarker2);
        $results         = $this->getQuestionMarkerService()->findQuestionMarkersByIds(array(1, 2));

        $this->assertNotNull($results);

    }

    public function testFindQuestionMarkersByMarkerId()
    {
        $questionMarker1 = array(
            'markerId'   => 1,
            'questionId' => 1,
            'type'       => 'Math'
        );

        $questionMarker2 = array(
            'markerId'   => 1,
            'questionId' => 2,
            'type'       => 'Math'
        );

        $questionMarker1 = $this->getQuestionMarkerService()->addQuestionMarker($questionMarker1);
        $questionMarker2 = $this->getQuestionMarkerService()->addQuestionMarker($questionMarker2);
        $results         = $this->getQuestionMarkerService()->findQuestionMarkersByMarkerId(1);
        $this->assertCount(2, $results);
    }

    public function testFindQuestionMarkersByQuestionId()
    {
        $questionMarker1 = array(
            'markerId'   => 1,
            'questionId' => 1,
            'type'       => 'Math'
        );

        $questionMarker2 = array(
            'markerId'   => 1,
            'questionId' => 2,
            'type'       => 'Math'
        );

        $questionMarker1 = $this->getQuestionMarkerService()->addQuestionMarker($questionMarker1);
        $questionMarker2 = $this->getQuestionMarkerService()->addQuestionMarker($questionMarker2);
        $results         = $this->getQuestionMarkerService()->findQuestionMarkersByQuestionId(1);
        $this->assertCount(1, $results);
    }

    public function testAddQuestionMarker()
    {
        $questionMarker = array(
            'markerId'   => 1,
            'questionId' => 1,
            'type'       => 'Math'
        );

        $result = $this->getQuestionMarkerService()->addQuestionMarker($questionMarker);

        $this->assertEquals('Math', $result['type']);
        $this->assertEquals(1, $result['markerId']);
        $this->assertEquals(1, $result['questionId']);

    }

    public function testUpdateQuestionMarker()
    {
        $questionMarker = array(
            'markerId'   => 1,
            'questionId' => 1,
            'type'       => 'Math'
        );

        $questionMarker = $this->getQuestionMarkerService()->addQuestionMarker($questionMarker);

        $updateQuestionMarker = array(
            'stem' => 'test1'
        );

        $result = $this->getQuestionMarkerService()->updateQuestionMarker($questionMarker['id'], $updateQuestionMarker);
        $this->assertEquals('test1', $result['stem']);
    }

    public function testDeleteQuestionMarker()
    {
        $questionMarker = array(
            'markerId'   => 1,
            'questionId' => 1,
            'type'       => 'Math'
        );

        $questionMarker = $this->getQuestionMarkerService()->addQuestionMarker($questionMarker);
        $resultStart    = $resultEnd    = $this->getQuestionMarkerService()->getQuestionMarker($questionMarker['id']);
        $this->getQuestionMarkerService()->deleteQuestionMarker($questionMarker['id']);
        $resultEnd = $this->getQuestionMarkerService()->getQuestionMarker($questionMarker['id']);
        $this->assertNotNull($resultStart);
        $this->assertNull($resultEnd);
    }

    public function testSearchQuestionMarkers()
    {
    }

    protected function getQuestionMarkerService()
    {
        return $this->getServiceKernel()->createService('Marker.QuestionMarkerService');
    }
}
