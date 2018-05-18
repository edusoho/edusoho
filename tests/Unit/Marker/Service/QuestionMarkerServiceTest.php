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
