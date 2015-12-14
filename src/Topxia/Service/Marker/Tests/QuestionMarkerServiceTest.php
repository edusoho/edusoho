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
        $questionMarker1 = array(
            'markerId'   => 1,
            'questionId' => 1,
            'type'       => 'Math',
            'stem'       => 'test1',
            'difficulty' => 'hard'
        );

        $questionMarker2 = array(
            'markerId'   => 1,
            'questionId' => 2,
            'type'       => 'Math',
            'stem'       => 'test2',
            'difficulty' => 'hard'
        );

        $questionMarker1 = $this->getQuestionMarkerService()->addQuestionMarker($questionMarker1);
        $questionMarker2 = $this->getQuestionMarkerService()->addQuestionMarker($questionMarker2);
        $conditions      = array(
            'difficulty' => 'hard'
        );
        $results = $this->getQuestionMarkerService()->searchQuestionMarkers($conditions, array('createdTime', 'DESC'), 0, 10);
        $this->assertCount(2, $results);
    }

    public function testSortQuestionMarker()
    {
        $questionMarker1 = array(
            'markerId'   => 1,
            'questionId' => 1,
            'type'       => 'Math',
            'stem'       => 'test1',
            'difficulty' => 'hard',
            'seq'        => 1
        );

        $questionMarker2 = array(
            'markerId'   => 1,
            'questionId' => 2,
            'type'       => 'Math',
            'stem'       => 'test1',
            'difficulty' => 'hard',
            'seq'        => 2
        );

        $questionMarker3 = array(
            'markerId'   => 1,
            'questionId' => 3,
            'type'       => 'Math',
            'stem'       => 'test2',
            'difficulty' => 'hard',
            'seq'        => 3
        );

        $questionMarker1 = $this->getQuestionMarkerService()->addQuestionMarker($questionMarker1);
        $questionMarker2 = $this->getQuestionMarkerService()->addQuestionMarker($questionMarker2);
        $questionMarker3 = $this->getQuestionMarkerService()->addQuestionMarker($questionMarker3);
        $ids             = array($questionMarker3['id'], $questionMarker1['id'], $questionMarker2['id']);
        $this->getQuestionMarkerService()->sortQuestionMarkers($ids);
        $results         = $this->getQuestionMarkerService()->findQuestionMarkersByMarkerId(1);
        $questionMarker3 = $this->getQuestionMarkerService()->getQuestionMarker($questionMarker3['id']);
        $this->assertEquals(1, $questionMarker3['seq']);
        $questionMarker1 = $this->getQuestionMarkerService()->getQuestionMarker($questionMarker1['id']);
        $this->assertEquals(2, $questionMarker1['seq']);

    }

    protected function getQuestionMarkerService()
    {
        return $this->getServiceKernel()->createService('Marker.QuestionMarkerService');
    }
}
