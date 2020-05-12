<?php

namespace Tests\Unit\Marker\Service;

use Biz\BaseTestCase;

class QuestionMarkerServiceTest extends BaseTestCase
{
    public function testGetQuestionMarker()
    {
        $this->mockBiz(
            'ItemBank:Item:ItemService',
            array(
                array(
                    'functionName' => 'getItemWithQuestions',
                    'returnValue' => array('id' => 1, 'questions' => array(array('id' => 1))),
                ),
            )
        );
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );

        $marker = $this->getMarkerService()->addMarker(1, $fields);
        $questionMarker = $this->getQuestionMarkerService()->addQuestionMarker(1, $marker['markerId'], 1);

        $result = $this->getQuestionMarkerService()->getQuestionMarker($questionMarker['id']);
        $this->assertEquals($marker['markerId'], $result['markerId']);
        $this->assertEquals(1, $result['questionId']);
    }

    public function testFindQuestionMarkersByIds()
    {
        $this->mockBiz(
            'ItemBank:Item:ItemService',
            array(
                array(
                    'functionName' => 'getItemWithQuestions',
                    'returnValue' => array('id' => 1, 'questions' => array(array('id' => 1))),
                ),
            )
        );
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );

        $this->getMarkerService()->addMarker(1, $fields);
        $questionMarker = $this->getQuestionMarkerService()->addQuestionMarker(1, 1, 1);
        $questionMarker1 = $this->getQuestionMarkerService()->addQuestionMarker(1, 1, 1);

        $results = $this->getQuestionMarkerService()->findQuestionMarkersByIds(array(1, 2));
        $this->assertNotNull($results);
    }

    public function testFindQuestionMarkersByMarkerId()
    {
        $this->mockBiz(
            'ItemBank:Item:ItemService',
            array(
                array(
                    'functionName' => 'getItemWithQuestions',
                    'returnValue' => array('id' => 1, 'questions' => array(array('id' => 1))),
                ),
            )
        );
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );

        $this->getMarkerService()->addMarker(1, $fields);
        $questionMarker = $this->getQuestionMarkerService()->addQuestionMarker(1, 1, 1);
        $questionMarker1 = $this->getQuestionMarkerService()->addQuestionMarker(1, 1, 1);
        $results = $this->getQuestionMarkerService()->findQuestionMarkersByMarkerId(1);
        $this->assertCount(3, $results);
    }

    public function testFindQuestionMarkerByMarkerIds()
    {
        $this->mockBiz(
            'ItemBank:Item:ItemService',
            array(
                array(
                    'functionName' => 'getItemWithQuestions',
                    'returnValue' => array('id' => 1, 'questions' => array(array('id' => 1))),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getItemWithQuestions',
                    'returnValue' => array('id' => 2, 'questions' => array(array('id' => 2))),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getItemWithQuestions',
                    'returnValue' => array('id' => 3, 'questions' => array(array('id' => 3))),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getItemWithQuestions',
                    'returnValue' => array('id' => 4, 'questions' => array(array('id' => 4))),
                    'runTimes' => 1,
                ),
            )
        );

        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );

        $this->getMarkerService()->addMarker(1, $fields);
        $this->getMarkerService()->addMarker(2, $fields);
        $this->getMarkerService()->addMarker(3, $fields);
        $questionMarker1 = $this->getQuestionMarkerService()->addQuestionMarker(1, 1, 1);
        $questionMarker2 = $this->getQuestionMarkerService()->addQuestionMarker(2, 2, 1);
        $questionMarker3 = $this->getQuestionMarkerService()->addQuestionMarker(3, 2, 2);
        $questionMarker4 = $this->getQuestionMarkerService()->addQuestionMarker(4, 3, 1);

        $result = $this->getQuestionMarkerService()->findQuestionMarkersByMarkerIds(array(1, 2));
        $this->assertCount(5, $result);
    }

    public function testFindQuestionMarkersMetaByMediaId()
    {
        $this->mockBiz(
            'ItemBank:Item:ItemService',
            array(
                array(
                    'functionName' => 'getItemWithQuestions',
                    'returnValue' => array('id' => 1, 'questions' => array(array('id' => 1))),
                ),
            )
        );
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );

        $this->mockBiz('File:UploadFileService', array(
            array(
                'functionName' => 'getFile',
                'returnValue' => array('id' => 1),
                'withParams' => array(1),
            ),
            array(
                'functionName' => 'getFile',
                'returnValue' => array('id' => 2),
                'withParams' => array(2),
            ),
        ));

        $this->getMarkerService()->addMarker(1, $fields);
        $this->getMarkerService()->addMarker(2, $fields);

        $this->assertEmpty($this->getQuestionMarkerService()->findQuestionMarkersMetaByMediaId(2333));

        $questionMarker = $this->getQuestionMarkerService()->addQuestionMarker(1, 1, 1);
        $questionMarker1 = $this->getQuestionMarkerService()->addQuestionMarker(1, 1, 1);

        $result = $this->getQuestionMarkerService()->findQuestionMarkersMetaByMediaId(1);
        $this->assertCount(3, $result);
    }

    public function testSearchQuestionMarkersCount()
    {
        $this->mockBiz(
            'ItemBank:Item:ItemService',
            array(
                array(
                    'functionName' => 'getItemWithQuestions',
                    'returnValue' => array('id' => 1, 'questions' => array(array('id' => 1))),
                ),
            )
        );
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );

        $this->getMarkerService()->addMarker(1, $fields);
        $this->getMarkerService()->addMarker(2, $fields);

        $questionMarker = $this->getQuestionMarkerService()->addQuestionMarker(1, 1, 1);
        $questionMarker1 = $this->getQuestionMarkerService()->addQuestionMarker(1, 1, 2);

        $count = $this->getQuestionMarkerService()->searchQuestionMarkersCount(array());
        $this->assertEquals(4, $count);

        $count = $this->getQuestionMarkerService()->searchQuestionMarkersCount(array('ids' => array(1, 2)));
        $this->assertEquals(2, $count);

        $count = $this->getQuestionMarkerService()->searchQuestionMarkersCount(array('seq' => 2));
        $this->assertEquals(1, $count);

        $count = $this->getQuestionMarkerService()->searchQuestionMarkersCount(array('markerId' => 1));
        $this->assertEquals(3, $count);

        $count = $this->getQuestionMarkerService()->searchQuestionMarkersCount(array('questionId' => 1));
        $this->assertEquals(4, $count);
    }

    public function testFindQuestionMarkersByQuestionId()
    {
        $this->mockBiz(
            'ItemBank:Item:ItemService',
            array(
                array(
                    'functionName' => 'getItemWithQuestions',
                    'returnValue' => array('id' => 1, 'questions' => array(array('id' => 1))),
                ),
            )
        );
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );

        $this->getMarkerService()->addMarker(1, $fields);
        $questionMarker = $this->getQuestionMarkerService()->addQuestionMarker(1, 1, 1);
        $questionMarker1 = $this->getQuestionMarkerService()->addQuestionMarker(1, 1, 1);
        $results = $this->getQuestionMarkerService()->findQuestionMarkersByQuestionId(1);
        $this->assertCount(3, $results);
    }

    public function testAddQuestionMarker()
    {
        $this->mockBiz(
            'ItemBank:Item:ItemService',
            array(
                array(
                    'functionName' => 'getItemWithQuestions',
                    'returnValue' => array('id' => 1, 'questions' => array(array('id' => 1))),
                ),
            )
        );
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );

        $this->getMarkerService()->addMarker(1, $fields);
        $question = $this->getQuestionMarkerService()->addQuestionMarker(1, 1, 1);
        $question1 = $this->getQuestionMarkerService()->addQuestionMarker(1, 1, 1);
        $questionMarker = $this->getQuestionMarkerService()->getQuestionMarker($question['id']);
        $questionMarker1 = $this->getQuestionMarkerService()->getQuestionMarker($question1['id']);

        $this->assertEquals($questionMarker['seq'], 2);
        $this->assertEquals($questionMarker1['seq'], 1);
    }

    public function testUpdateQuestionMarker()
    {
        $this->mockBiz(
            'ItemBank:Item:ItemService',
            array(
                array(
                    'functionName' => 'getItemWithQuestions',
                    'returnValue' => array('id' => 1, 'questions' => array(array('id' => 1))),
                ),
            )
        );
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );

        $this->getMarkerService()->addMarker(1, $fields);
        $questionMarker = $this->getQuestionMarkerService()->addQuestionMarker(1, 1, 1);

        $updateQuestionMarker = array(
            'stem' => 'test1',
            'metas' => array('爱', '测', '额', 'U'),
        );

        $result = $this->getQuestionMarkerService()->updateQuestionMarker($questionMarker['id'], $updateQuestionMarker);
        $this->assertEquals('test1', $result['stem']);
    }

    public function testDeleteQuestionMarker()
    {
        $this->mockBiz(
            'ItemBank:Item:ItemService',
            array(
                array(
                    'functionName' => 'getItemWithQuestions',
                    'returnValue' => array('id' => 1, 'questions' => array(array('id' => 1))),
                ),
            )
        );
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );

        $this->getMarkerService()->addMarker(1, $fields);
        $questionMarker = $this->getQuestionMarkerService()->addQuestionMarker(1, 1, 1);
        $resultStart = $this->getQuestionMarkerService()->getQuestionMarker($questionMarker['id']);
        $this->getQuestionMarkerService()->deleteQuestionMarker($questionMarker['id']);
        $resultEnd = $this->getQuestionMarkerService()->getQuestionMarker($questionMarker['id']);
        $this->assertNotNull($resultStart);
        $this->assertNull($resultEnd);
    }

    public function testSearchQuestionMarkers()
    {
        $this->mockBiz(
            'ItemBank:Item:ItemService',
            array(
                array(
                    'functionName' => 'getItemWithQuestions',
                    'returnValue' => array('id' => 1, 'questions' => array(array('id' => 1))),
                ),
            )
        );
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );

        $this->getMarkerService()->addMarker(1, $fields);
        $question = $this->getQuestionMarkerService()->addQuestionMarker(1, 1, 1);
        $question1 = $this->getQuestionMarkerService()->addQuestionMarker(1, 1, 1);

        $conditions = array(
            'questionId' => 1,
        );
        $results = $this->getQuestionMarkerService()->searchQuestionMarkers($conditions, array('createdTime' => 'DESC'), 0, 10);
        $this->assertCount(3, $results);
    }

    public function testSortQuestionMarker()
    {
        $this->mockBiz(
            'ItemBank:Item:ItemService',
            array(
                array(
                    'functionName' => 'getItemWithQuestions',
                    'returnValue' => array('id' => 1, 'questions' => array(array('id' => 1))),
                ),
            )
        );
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );

        $this->getMarkerService()->addMarker(1, $fields);
        $questionMarker1 = $this->getQuestionMarkerService()->addQuestionMarker(1, 1, 3);
        $questionMarker2 = $this->getQuestionMarkerService()->addQuestionMarker(1, 1, 1);
        $questionMarker3 = $this->getQuestionMarkerService()->addQuestionMarker(1, 1, 2);
        $ids = array($questionMarker3['id'], $questionMarker1['id'], $questionMarker2['id']);
        $this->getQuestionMarkerService()->sortQuestionMarkers($ids);
        $results = $this->getQuestionMarkerService()->findQuestionMarkersByMarkerId(1);
        $questionMarker3 = $this->getQuestionMarkerService()->getQuestionMarker($questionMarker3['id']);
        $this->assertEquals(1, $questionMarker3['seq']);
        $questionMarker1 = $this->getQuestionMarkerService()->getQuestionMarker($questionMarker1['id']);
        $this->assertEquals(2, $questionMarker1['seq']);
    }

    public function testMerge()
    {
        $this->mockBiz(
            'ItemBank:Item:ItemService',
            array(
                array(
                    'functionName' => 'getItemWithQuestions',
                    'returnValue' => array('id' => 1, 'questions' => array(array('id' => 1))),
                ),
            )
        );
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );

        $this->getMarkerService()->addMarker(1, $fields);
        $this->getMarkerService()->addMarker(2, $fields);

        $questionMarker = $this->getQuestionMarkerService()->addQuestionMarker(1, 1, 1);
        $questionMarker1 = $this->getQuestionMarkerService()->addQuestionMarker(1, 2, 2);

        $this->getQuestionMarkerService()->merge($questionMarker['markerId'], $questionMarker1['markerId']);
        $questionMarkerAfter = $this->getQuestionMarkerService()->getQuestionMarker($questionMarker['id']);
        $this->assertEquals(2, ($questionMarkerAfter['seq'] - $questionMarker['seq']));
    }

    protected function getQuestionMarkerService()
    {
        return $this->createService('Marker:QuestionMarkerService');
    }

    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    protected function getMarkerService()
    {
        return $this->createService('Marker:MarkerService');
    }
}
