<?php

namespace Tests\Unit\Marker\Service;

use Biz\BaseTestCase;

class QuestionMarkerServiceTest extends BaseTestCase
{
    public function testGetQuestionMarker()
    {
        $question = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty' => 'normal',
            'answer' => array('answer'),
            'target' => 'course-1',
            'choices' => array('爱', '测', '额', '恶'),
            'uncertain' => 0,
            'analysis' => '',
            'score' => '2',
            'submission' => 'submit',
            'parentId' => 0,
            'copyId' => 1,
            'courseSetId' => 1,
        );
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );

        $question = $this->getQuestionService()->create($question);
        $this->getMarkerService()->addMarker(1, $fields);
        $questionMarker = $this->getQuestionMarkerService()->addQuestionMarker($question['id'], 1, 1);

        $result = $this->getQuestionMarkerService()->getQuestionMarker($questionMarker['id']);
        $this->assertEquals('single_choice', $result['type']);
        $this->assertEquals(1, $result['markerId']);
        $this->assertEquals($question['id'], $result['questionId']);
    }

    public function testFindQuestionMarkersByIds()
    {
        $question = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty' => 'normal',
            'answer' => array('answer'),
            'target' => 'course-1',
            'choices' => array('爱', '测', '额', '恶'),
            'uncertain' => 0,
            'analysis' => '',
            'score' => '2',
            'submission' => 'submit',
            'parentId' => 0,
            'copyId' => 1,
            'courseSetId' => 1,
        );
        $question1 = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty' => 'normal',
            'answer' => array('answer'),
            'target' => 'course-1',
            'choices' => array('爱', '测', '额', '恶'),
            'uncertain' => 0,
            'analysis' => '',
            'score' => '2',
            'submission' => 'submit',
            'parentId' => 0,
            'copyId' => 1,
            'courseSetId' => 1,
        );
        $question = $this->getQuestionService()->create($question);
        $question1 = $this->getQuestionService()->create($question1);
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );

        $this->getMarkerService()->addMarker(1, $fields);
        $questionMarker = $this->getQuestionMarkerService()->addQuestionMarker($question['id'], 1, 1);
        $questionMarker1 = $this->getQuestionMarkerService()->addQuestionMarker($question1['id'], 1, 1);

        $results = $this->getQuestionMarkerService()->findQuestionMarkersByIds(array(1, 2));
        $this->assertNotNull($results);
    }

    public function testFindQuestionMarkersByMarkerId()
    {
        $question = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty' => 'normal',
            'answer' => array('answer'),
            'target' => 'course-1',
            'choices' => array('爱', '测', '额', '恶'),
            'uncertain' => 0,
            'analysis' => '',
            'score' => '2',
            'submission' => 'submit',
            'parentId' => 0,
            'copyId' => 1,
            'courseSetId' => 1,
        );
        $question1 = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty' => 'normal',
            'answer' => array('answer'),
            'target' => 'course-1',
            'choices' => array('爱', '测', '额', '恶'),
            'uncertain' => 0,
            'analysis' => '',
            'score' => '2',
            'submission' => 'submit',
            'parentId' => 0,
            'copyId' => 1,
            'courseSetId' => 1,
        );
        $question = $this->getQuestionService()->create($question);
        $question1 = $this->getQuestionService()->create($question1);
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );

        $this->getMarkerService()->addMarker(1, $fields);
        $questionMarker = $this->getQuestionMarkerService()->addQuestionMarker($question['id'], 1, 1);
        $questionMarker1 = $this->getQuestionMarkerService()->addQuestionMarker($question1['id'], 1, 1);
        $results = $this->getQuestionMarkerService()->findQuestionMarkersByMarkerId(1);
        $this->assertCount(3, $results);
    }

    public function testFindQuestionMarkerByMarkerIds()
    {
        $question1 = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty' => 'normal',
            'answer' => array('answer'),
            'target' => 'course-1',
            'choices' => array('爱', '测', '额', '恶'),
            'uncertain' => 0,
            'analysis' => '',
            'score' => '2',
            'submission' => 'submit',
            'parentId' => 0,
            'copyId' => 1,
            'courseSetId' => 1,
        );
        $question2 = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty' => 'normal',
            'answer' => array('answer'),
            'target' => 'course-1',
            'choices' => array('爱', '测', '额', '恶'),
            'uncertain' => 0,
            'analysis' => '',
            'score' => '2',
            'submission' => 'submit',
            'parentId' => 0,
            'copyId' => 1,
            'courseSetId' => 1,
        );
        $question3 = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty' => 'normal',
            'answer' => array('answer'),
            'target' => 'course-1',
            'choices' => array('爱', '测', '额', '恶'),
            'uncertain' => 0,
            'analysis' => '',
            'score' => '2',
            'submission' => 'submit',
            'parentId' => 0,
            'copyId' => 1,
            'courseSetId' => 1,
        );
        $question4 = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty' => 'normal',
            'answer' => array('answer'),
            'target' => 'course-1',
            'choices' => array('爱', '测', '额', '恶'),
            'uncertain' => 0,
            'analysis' => '',
            'score' => '2',
            'submission' => 'submit',
            'parentId' => 0,
            'copyId' => 1,
            'courseSetId' => 1,
        );
        $question1 = $this->getQuestionService()->create($question1);
        $question2 = $this->getQuestionService()->create($question2);
        $question3 = $this->getQuestionService()->create($question3);
        $question4 = $this->getQuestionService()->create($question4);

        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );

        $this->getMarkerService()->addMarker(1, $fields);
        $this->getMarkerService()->addMarker(2, $fields);
        $this->getMarkerService()->addMarker(3, $fields);
        $questionMarker1 = $this->getQuestionMarkerService()->addQuestionMarker($question1['id'], 1, 1);
        $questionMarker2 = $this->getQuestionMarkerService()->addQuestionMarker($question2['id'], 2, 1);
        $questionMarker3 = $this->getQuestionMarkerService()->addQuestionMarker($question3['id'], 2, 2);
        $questionMarker4 = $this->getQuestionMarkerService()->addQuestionMarker($question4['id'], 3, 1);

        $result = $this->getQuestionMarkerService()->findQuestionMarkersByMarkerIds(array(1, 2));
        $this->assertCount(5, $result);
    }

    public function testFindQuestionMarkersMetaByMediaId()
    {
        $question = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty' => 'normal',
            'answer' => array('answer'),
            'target' => 'course-1',
            'choices' => array('爱', '测', '额', '恶'),
            'uncertain' => 0,
            'analysis' => '',
            'score' => '2',
            'submission' => 'submit',
            'parentId' => 0,
            'copyId' => 1,
            'courseSetId' => 1,
        );
        $question1 = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty' => 'normal',
            'answer' => array('answer'),
            'target' => 'course-1',
            'choices' => array('爱', '测', '额', '恶'),
            'uncertain' => 0,
            'analysis' => '',
            'score' => '2',
            'submission' => 'submit',
            'parentId' => 0,
            'copyId' => 1,
            'courseSetId' => 1,
        );
        $question = $this->getQuestionService()->create($question);
        $question1 = $this->getQuestionService()->create($question1);
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

        $questionMarker = $this->getQuestionMarkerService()->addQuestionMarker($question['id'], 1, 1);
        $questionMarker1 = $this->getQuestionMarkerService()->addQuestionMarker($question1['id'], 1, 1);

        $result = $this->getQuestionMarkerService()->findQuestionMarkersMetaByMediaId(1);
        $this->assertCount(3, $result);
    }

    public function testSearchQuestionMarkersCount()
    {
        $question = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty' => 'normal',
            'answer' => array('answer'),
            'target' => 'course-1',
            'choices' => array('爱', '测', '额', '恶'),
            'uncertain' => 0,
            'analysis' => '',
            'score' => '2',
            'submission' => 'submit',
            'parentId' => 0,
            'copyId' => 1,
            'courseSetId' => 1,
        );
        $question1 = array(
            'type' => 'choice',
            'stem' => 'adealllll',
            'difficulty' => 'difficult',
            'answer' => array('answer'),
            'target' => 'course-1',
            'choices' => array('爱', '测', '额', '恶'),
            'uncertain' => 0,
            'analysis' => '',
            'score' => '2',
            'submission' => 'submit',
            'parentId' => 0,
            'copyId' => 1,
            'courseSetId' => 1,
        );
        $question = $this->getQuestionService()->create($question);
        $question1 = $this->getQuestionService()->create($question1);
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );

        $this->getMarkerService()->addMarker(1, $fields);
        $this->getMarkerService()->addMarker(2, $fields);

        $questionMarker = $this->getQuestionMarkerService()->addQuestionMarker($question['id'], 1, 1);
        $questionMarker1 = $this->getQuestionMarkerService()->addQuestionMarker($question1['id'], 1, 2);

        $count = $this->getQuestionMarkerService()->searchQuestionMarkersCount(array());
        $this->assertEquals(4, $count);

        $count = $this->getQuestionMarkerService()->searchQuestionMarkersCount(array('ids' => array(1, 2)));
        $this->assertEquals(2, $count);

        $count = $this->getQuestionMarkerService()->searchQuestionMarkersCount(array('seq' => 2));
        $this->assertEquals(1, $count);

        $count = $this->getQuestionMarkerService()->searchQuestionMarkersCount(array('markerId' => 1));
        $this->assertEquals(3, $count);

        $count = $this->getQuestionMarkerService()->searchQuestionMarkersCount(array('questionId' => $question1['id']));
        $this->assertEquals(1, $count);

        $count = $this->getQuestionMarkerService()->searchQuestionMarkersCount(array('difficulty' => 'difficult'));
        $this->assertEquals(1, $count);

        $count = $this->getQuestionMarkerService()->searchQuestionMarkersCount(array('type' => 'choice'));
        $this->assertEquals(1, $count);

        $count = $this->getQuestionMarkerService()->searchQuestionMarkersCount(array('stem' => 'lll'));
        $this->assertEquals(1, $count);
    }

    public function testFindQuestionMarkersByQuestionId()
    {
        $question = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty' => 'normal',
            'answer' => array('answer'),
            'target' => 'course-1',
            'choices' => array('爱', '测', '额', '恶'),
            'uncertain' => 0,
            'analysis' => '',
            'score' => '2',
            'submission' => 'submit',
            'parentId' => 0,
            'copyId' => 1,
            'courseSetId' => 1,
        );
        $question1 = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty' => 'normal',
            'answer' => array('answer'),
            'target' => 'course-1',
            'choices' => array('爱', '测', '额', '恶'),
            'uncertain' => 0,
            'analysis' => '',
            'score' => '2',
            'submission' => 'submit',
            'parentId' => 0,
            'copyId' => 1,
            'courseSetId' => 1,
        );
        $question = $this->getQuestionService()->create($question);
        $question1 = $this->getQuestionService()->create($question1);
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );

        $this->getMarkerService()->addMarker(1, $fields);
        $questionMarker = $this->getQuestionMarkerService()->addQuestionMarker($question['id'], 1, 1);
        $questionMarker1 = $this->getQuestionMarkerService()->addQuestionMarker($question1['id'], 1, 1);
        $results = $this->getQuestionMarkerService()->findQuestionMarkersByQuestionId($question['id']);
        $this->assertCount(2, $results);
    }

    public function testAddQuestionMarker()
    {
        $question = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty' => 'normal',
            'answer' => array('answer'),
            'target' => 'course-1',
            'choices' => array('爱', '测', '额', '恶'),
            'uncertain' => 0,
            'analysis' => '',
            'score' => '2',
            'submission' => 'submit',
            'parentId' => 0,
            'copyId' => 1,
            'courseSetId' => 1,
        );
        $question1 = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty' => 'normal',
            'answer' => array('answer'),
            'target' => 'course-1',
            'choices' => array('爱', '测', '额', '恶'),
            'uncertain' => 0,
            'analysis' => '',
            'score' => '2',
            'submission' => 'submit',
            'parentId' => 1,
            'copyId' => 1,
            'courseSetId' => 1,
        );
        $question = $this->getQuestionService()->create($question);
        $question1 = $this->getQuestionService()->create($question1);
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );

        $this->getMarkerService()->addMarker(1, $fields);
        $question = $this->getQuestionMarkerService()->addQuestionMarker($question['id'], 1, 1);
        $question1 = $this->getQuestionMarkerService()->addQuestionMarker($question1['id'], 1, 1);
        $questionMarker = $this->getQuestionMarkerService()->getQuestionMarker($question['id']);
        $questionMarker1 = $this->getQuestionMarkerService()->getQuestionMarker($question1['id']);

        $this->assertEquals($questionMarker['seq'], 2);
        $this->assertEquals($questionMarker1['seq'], 1);
    }

    public function testUpdateQuestionMarker()
    {
        $question = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty' => 'normal',
            'answer' => array('answer'),
            'target' => 'course-1',
            'choices' => array('爱', '测', '额', '恶'),
            'uncertain' => 0,
            'analysis' => '',
            'score' => '2',
            'submission' => 'submit',
            'parentId' => 0,
            'copyId' => 1,
            'courseSetId' => 1,
        );
        $question = $this->getQuestionService()->create($question);
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );

        $this->getMarkerService()->addMarker(1, $fields);
        $questionMarker = $this->getQuestionMarkerService()->addQuestionMarker($question['id'], 1, 1);

        $updateQuestionMarker = array(
            'stem' => 'test1',
            'metas' => array('爱', '测', '额', 'U'),
        );

        $result = $this->getQuestionMarkerService()->updateQuestionMarker($questionMarker['id'], $updateQuestionMarker);
        $this->assertEquals('test1', $result['stem']);
    }

    public function testDeleteQuestionMarker()
    {
        $question = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty' => 'normal',
            'answer' => array('answer'),
            'target' => 'course-1',
            'choices' => array('爱', '测', '额', '恶'),
            'uncertain' => 0,
            'analysis' => '',
            'score' => '2',
            'submission' => 'submit',
            'parentId' => 0,
            'copyId' => 1,
            'courseSetId' => 1,
        );
        $question = $this->getQuestionService()->create($question);
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );

        $this->getMarkerService()->addMarker(1, $fields);
        $questionMarker = $this->getQuestionMarkerService()->addQuestionMarker($question['id'], 1, 1);
        $resultStart = $this->getQuestionMarkerService()->getQuestionMarker($questionMarker['id']);
        $this->getQuestionMarkerService()->deleteQuestionMarker($questionMarker['id']);
        $resultEnd = $this->getQuestionMarkerService()->getQuestionMarker($questionMarker['id']);
        $this->assertNotNull($resultStart);
        $this->assertNull($resultEnd);
    }

    public function testSearchQuestionMarkers()
    {
        $question = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty' => 'hard',
            'answer' => array('answer'),
            'target' => 'course-1',
            'choices' => array('爱', '测', '额', '恶'),
            'uncertain' => 0,
            'analysis' => '',
            'score' => '2',
            'submission' => 'submit',
            'parentId' => 0,
            'copyId' => 1,
            'courseSetId' => 1,
        );
        $question1 = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty' => 'hard',
            'answer' => array('answer'),
            'target' => 'course-1',
            'choices' => array('爱', '测', '额', '恶'),
            'uncertain' => 0,
            'analysis' => '',
            'score' => '2',
            'submission' => 'submit',
            'parentId' => 1,
            'copyId' => 1,
            'courseSetId' => 1,
        );
        $question = $this->getQuestionService()->create($question);
        $question1 = $this->getQuestionService()->create($question1);
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );

        $this->getMarkerService()->addMarker(1, $fields);
        $question = $this->getQuestionMarkerService()->addQuestionMarker($question['id'], 1, 1);
        $question1 = $this->getQuestionMarkerService()->addQuestionMarker($question1['id'], 1, 1);

        $conditions = array(
            'difficulty' => 'hard',
        );
        $results = $this->getQuestionMarkerService()->searchQuestionMarkers($conditions, array('createdTime' => 'DESC'), 0, 10);
        $this->assertCount(3, $results);
    }

    public function testSortQuestionMarker()
    {
        $question = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty' => 'hard',
            'answer' => array('answer'),
            'target' => 'course-1',
            'choices' => array('爱', '测', '额', '恶'),
            'uncertain' => 0,
            'analysis' => '',
            'score' => '2',
            'submission' => 'submit',
            'parentId' => 0,
            'copyId' => 1,
            'courseSetId' => 1,
        );
        $question1 = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty' => 'hard',
            'answer' => array('answer'),
            'target' => 'course-1',
            'choices' => array('爱', '测', '额', '恶'),
            'uncertain' => 0,
            'analysis' => '',
            'score' => '2',
            'submission' => 'submit',
            'parentId' => 1,
            'copyId' => 1,
            'courseSetId' => 1,
        );
        $question2 = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty' => 'hard',
            'answer' => array('answer'),
            'target' => 'course-1',
            'choices' => array('爱', '测', '额', '恶'),
            'uncertain' => 0,
            'analysis' => '',
            'score' => '2',
            'submission' => 'submit',
            'parentId' => 1,
            'copyId' => 1,
            'courseSetId' => 1,
        );
        $question = $this->getQuestionService()->create($question);
        $question1 = $this->getQuestionService()->create($question1);
        $question2 = $this->getQuestionService()->create($question2);
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );

        $this->getMarkerService()->addMarker(1, $fields);
        $questionMarker1 = $this->getQuestionMarkerService()->addQuestionMarker($question['id'], 1, 3);
        $questionMarker2 = $this->getQuestionMarkerService()->addQuestionMarker($question1['id'], 1, 1);
        $questionMarker3 = $this->getQuestionMarkerService()->addQuestionMarker($question2['id'], 1, 2);
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
        $question = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty' => 'normal',
            'answer' => array('answer'),
            'target' => 'course-1',
            'choices' => array('爱', '测', '额', '恶'),
            'uncertain' => 0,
            'analysis' => '',
            'score' => '2',
            'submission' => 'submit',
            'parentId' => 0,
            'copyId' => 1,
            'courseSetId' => 1,
        );
        $question1 = array(
            'type' => 'choice',
            'stem' => 'adealllll',
            'difficulty' => 'difficult',
            'answer' => array('answer'),
            'target' => 'course-1',
            'choices' => array('爱', '测', '额', '恶'),
            'uncertain' => 0,
            'analysis' => '',
            'score' => '2',
            'submission' => 'submit',
            'parentId' => 0,
            'copyId' => 1,
            'courseSetId' => 1,
        );
        $question = $this->getQuestionService()->create($question);
        $question1 = $this->getQuestionService()->create($question1);
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );

        $this->getMarkerService()->addMarker(1, $fields);
        $this->getMarkerService()->addMarker(2, $fields);

        $questionMarker = $this->getQuestionMarkerService()->addQuestionMarker($question['id'], 1, 1);
        $questionMarker1 = $this->getQuestionMarkerService()->addQuestionMarker($question1['id'], 2, 2);

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
