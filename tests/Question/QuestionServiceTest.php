<?php

namespace Tests\Question;

use Topxia\Service\Common\BaseTestCase;

class QuestionServiceTest extends BaseTestCase
{
    public function testGet()
    {
        $question = $this->createQuestion();

        $questionOne = $this->getQuestionService()->get($question['id']);

        $this->assertEquals($question['stem'], $questionOne['stem']);
        $this->assertEquals($question['type'], $questionOne['type']);
    }

    public function testCreate()
    {
        $question = $this->createQuestion();

        $questionOne = $this->getQuestionService()->get($question['id']);

        $this->assertEquals($question['stem'], $questionOne['stem']);
        $this->assertEquals($question['type'], $questionOne['type']);
    }

    public function testUpdate()
    {
        $question = $this->createQuestion();
        $update   = array(
            'stem'   => 'update test single choice question 1.',
            'answer' => array('2'),
            'score'  => '2'
        );

        $questionUpdate = $this->getQuestionService()->update($question['id'], $update);

        $this->assertEquals($update['stem'], $questionUpdate['stem']);
        $this->assertEquals($update['score'], $questionUpdate['score']);
        $this->assertArrayEquals($update['answer'], $questionUpdate['answer']);
    }

    public function testDelete()
    {
        $question = $this->createQuestion();

        $this->getQuestionService()->delete($question['id']);

        $question = $this->getQuestionService()->get($question['id']);

        $this->assertNull($question);
    }

    public function testDeleteSubQuestions()
    {
        $material = $this->createMaterialQuestion();

        $this->getQuestionService()->deleteSubQuestions($material['id']);

        $materialSubs = $this->getQuestionService()->findQuestionsByParentId($material['id']);

        $this->assertTrue(empty($materialSubs));
    }

    public function testFindQuestionsByIds()
    {
        $question1 = $this->createQuestion();
        $question2 = $this->createQuestion1();
        $question3 = $this->createQuestion2();

        $ids = array($question1['id'], $question2['id'], $question3['id']);

        $questions = $this->getQuestionService()->findQuestionsByIds($ids);

        $this->assertCount(count($ids), $questions);
    }

    /*public function findQuestionsByParentId($id);

    public function search($conditions, $sort, $start, $limit);

    public function searchCount($conditions);

    public function waveCount($id, $diffs);

    public function judgeQuestion($question, $answer);

    public function hasEssay($questionIds);

    public function getQuestionCountGroupByTypes($conditions);*/

    /**
     * @expectedException \Topxia\Common\Exception\InvalidArgumentException
     */
    /*public function testCreateActivityWhenInvalidArgument()
    {
    $activity = array(
    'title' => 'test activity'
    );
    $savedActivity = $this->getActivityService()->createActivity($activity);
    $this->assertEquals($activity['title'], $savedActivity['title']);
    }*/

    protected function createQuestion()
    {
        $question = array(
            'type'     => 'single_choice',
            'stem'     => 'test single choice question 1.',
            'courseId' => 1,
            'lessonId' => 0,
            'choices'  => array(
                'question 1 -> choice 1',
                'question 1 -> choice 2',
                'question 1 -> choice 3',
                'question 1 -> choice 4'
            ),
            'answer'   => array(1),
            'target'   => 'course-1'
        );

        return $this->getQuestionService()->create($question);
    }

    protected function createQuestion1()
    {
        $question = array(
            'type'     => 'determine',
            'stem'     => 'test material-determine question.',
            'courseId' => 1,
            'lessonId' => 0,
            'answer'   => array(1),
            'target'   => 'course-1'
        );

        return $this->getQuestionService()->create($question);
    }

    protected function createQuestion2()
    {
        $question = array(
            'type'     => 'fill',
            'stem'     => 'fill[[a|b]]',
            'courseId' => 1,
            'lessonId' => 0,
            'answer'   => array(array('a', 'b')),
            'target'   => 'course-1'
        );

        return $this->getQuestionService()->create($question);
    }

    protected function createMaterialQuestion()
    {
        $material = array(
            'type'     => 'material',
            'stem'     => 'test material question.',
            'courseId' => 1,
            'lessonId' => 0,
            'answer'   => array(),
            'target'   => 'course-1'
        );
        $questionParent = $this->getQuestionService()->create($material);

        $single = array(
            'type'     => 'single_choice',
            'stem'     => 'test material-single choice question.',
            'courseId' => 1,
            'lessonId' => 0,
            'choices'  => array(
                'question 1 -> choice 1',
                'question 1 -> choice 2',
                'question 1 -> choice 3',
                'question 1 -> choice 4'
            ),
            'answer'   => array(1),
            'target'   => 'course-1',
            'parentId' => $questionParent['id']
        );

        $subQuestion1 = $this->getQuestionService()->create($single);

        $determine = array(
            'type'     => 'determine',
            'stem'     => 'test material-determine question.',
            'courseId' => 1,
            'lessonId' => 0,
            'answer'   => array(1),
            'target'   => 'course-1',
            'parentId' => $questionParent['id']
        );

        $subQuestion2 = $this->getQuestionService()->create($determine);

        return $questionParent;
    }

    protected function getQuestionService()
    {
        return $this->getBiz()->service('Question:QuestionService');
    }
}
