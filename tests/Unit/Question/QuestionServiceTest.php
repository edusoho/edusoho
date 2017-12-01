<?php

namespace Tests\Unit\Question;

use Biz\BaseTestCase;

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

    public function testBatchCreateQuestions()
    {
        $result = $this->getQuestionService()->batchCreateQuestions(array());
        $this->assertEmpty($result);

        $questions[] = array(
            'type' => 'single_choice',
            'stem' => 'test single choice question 1.',
            'courseId' => 1,
            'courseSetId' => 1,
            'lessonId' => 0,
            'metas' => array('choices' => array(
                'question 1 -> choice 1',
                'question 1 -> choice 2',
                'question 1 -> choice 3',
                'question 1 -> choice 4',
            )),
            'answer' => array(1),
            'target' => 'course-1',
        );

        $questions[] = array(
            'type' => 'determine',
            'stem' => 'test material-determine question.',
            'courseId' => 1,
            'courseSetId' => 1,
            'lessonId' => 0,
            'metas' => array(),
            'answer' => array(1),
            'target' => 'course-1',
        );

        $questions[] = array(
            'type' => 'fill',
            'stem' => 'fill[[a|b]]',
            'courseId' => 1,
            'courseSetId' => 1,
            'lessonId' => 0,
            'metas' => array(),
            'answer' => array(array('a', 'b')),
            'target' => 'course-1',
        );
        $time = time();

        $this->getQuestionService()->batchCreateQuestions($questions);

        $questions = $this->getQuestionService()->findQuestionsByCourseSetId(1);

        $createdTime = $questions[0]['createdTime'];

        $this->assertEquals(3, count($questions));
        $this->assertEquals($time, $createdTime);
    }

    /**
     * @expectedException \AppBundle\Common\Exception\ResourceNotFoundException
     */
    public function testUpdate()
    {
        $question = $this->createQuestion1();
        $update = array(
            'stem' => 'update test single choice question 1.',
            'content' => 'question content',
            'answer' => array('2'),
            'score' => '2',
        );

        $questionUpdate = $this->getQuestionService()->update($question['id'], $update);

        $this->assertEquals($update['stem'], $questionUpdate['stem']);
        $this->assertEquals($update['score'], $questionUpdate['score']);
        $this->assertArrayEquals($update['answer'], $questionUpdate['answer']);

        $questionUpdate = $this->getQuestionService()->update(123, $update);
    }

    public function testChildUpdate()
    {
        $material = array(
            'type' => 'material',
            'stem' => 'test material question.',
            'content' => 'question material content',
            'courseId' => 1,
            'courseSetId' => 1,
            'lessonId' => 2,
            'answer' => array(),
            'target' => 'course-1',
        );
        $questionParent = $this->getQuestionService()->create($material);

        $single = array(
            'type' => 'single_choice',
            'stem' => 'test material-single choice question.',
            'content' => 'question material-single_choice content',
            'courseSetId' => 1,
            'choices' => array(
                'question 1 -> choice 1',
                'question 1 -> choice 2',
                'question 1 -> choice 3',
                'question 1 -> choice 4',
            ),
            'answer' => array(1),
            'target' => 'course-1',
            'parentId' => $questionParent['id'],
        );

        $subQuestion1 = $this->getQuestionService()->create($single);

        $update = array(
            'stem' => 'update test child single choice question.',
            'answer' => array('2'),
            'score' => '2',
        );

        $questionUpdate = $this->getQuestionService()->update($subQuestion1['id'], $update);

        $this->assertEquals($update['stem'], $questionUpdate['stem']);
        $this->assertEquals($update['score'], $questionUpdate['score']);
        $this->assertArrayEquals($update['answer'], $questionUpdate['answer']);
        $this->assertEquals(1, $questionUpdate['courseId']);
        $this->assertEquals(2, $questionUpdate['lessonId']);
    }

    public function testUpdateCopyQuestionsSubCount()
    {
        $material = array(
            'type' => 'material',
            'stem' => 'test material question.',
            'content' => 'question material content',
            'courseId' => 1,
            'courseSetId' => 1,
            'lessonId' => 2,
            'answer' => array(),
            'target' => 'course-1',
            'subCount' => 5,
        );
        $questionParent = $this->getQuestionService()->create($material);

        $copyMaterial = array(
            'type' => 'material',
            'stem' => 'test material question.',
            'content' => 'question material content',
            'courseId' => 1,
            'courseSetId' => 1,
            'lessonId' => 2,
            'answer' => array(),
            'target' => 'course-1',
            'subCount' => 4,
            'copyId' => $questionParent['id'],
        );
        $copy = $this->getQuestionService()->create($copyMaterial);

        $this->getQuestionService()->updateCopyQuestionsSubCount($questionParent['id'], $questionParent['subCount']);

        $result = $this->getQuestionService()->get($copy['id']);

        $this->assertEquals(5, $result['subCount']);
    }

    public function testDelete()
    {
        $result = $this->getQuestionService()->delete(123);
        $this->assertFalse($result);

        $question = $this->createQuestion1();
        $this->getQuestionService()->delete($question['id']);
        $question = $this->getQuestionService()->get($question['id']);

        $this->assertNull($question);
    }

    public function testDeleteMaterial()
    {
        $material = $this->createMaterialQuestion();
        $this->assertEquals(2, $material['subCount']);

        $this->getQuestionService()->delete($material['id']);
        $subQuestions = $this->getQuestionService()->findQuestionsByParentId($material['id']);
        $material = $this->getQuestionService()->get($material['id']);

        $this->assertEquals(0, count($subQuestions));
        $this->assertNull($material);
    }

    public function testDeleteSubQuestion()
    {
        $material = $this->createMaterialQuestion();
        $this->assertEquals(2, $material['subCount']);

        $subQuestions = $this->getQuestionService()->findQuestionsByParentId($material['id']);

        $this->getQuestionService()->delete($subQuestions[0]['id']);

        $question = $this->getQuestionService()->get($material['id']);
        $subQuestion = $this->getQuestionService()->get($subQuestions[0]['id']);

        $this->assertEquals(1, $question['subCount']);
        $this->assertNull($subQuestion);
    }

    public function testBatchDeletes()
    {
        $result = $this->getQuestionService()->batchDeletes(array());
        $this->assertFalse($result);

        $question1 = $this->createQuestion1();
        $question2 = $this->createQuestion2();

        $ids = array($question1['id'], $question2['id']);
        $result = $this->getQuestionService()->batchDeletes($ids);

        $this->assertTrue($result);
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

    public function testFindQuestionsByParentId()
    {
        $parentQuestion = $this->createMaterialQuestion();

        $subs = $this->getQuestionService()->findQuestionsByParentId($parentQuestion['id']);

        $this->assertEquals(2, count($subs));
    }

    public function testFindQuestionsByCourseSetId()
    {
        $question1 = $this->createQuestion();
        $question2 = $this->createQuestion1();
        $question3 = $this->createQuestion3();

        $questions = $this->getQuestionService()->findQuestionsByCourseSetId(1);

        $this->assertEquals(2, count($questions));
    }

    public function testFindQuestionsByCopyId()
    {
        $question = $this->createQuestion();
        $copy1 = array(
            'type' => 'single_choice',
            'stem' => 'test single choice question 1.',
            'content' => 'question content',
            'courseId' => 1,
            'courseSetId' => 1,
            'lessonId' => 0,
            'choices' => array(
                'question 1 -> choice 1',
                'question 1 -> choice 2',
                'question 1 -> choice 3',
                'question 1 -> choice 4',
            ),
            'answer' => array(1),
            'target' => 'course-1',
            'copyId' => $question['id'],
        );
        $this->getQuestionService()->create($copy1);

        $copy2 = array(
            'type' => 'single_choice',
            'stem' => 'test single choice question 1.',
            'content' => 'question content',
            'courseId' => 1,
            'courseSetId' => 1,
            'lessonId' => 0,
            'choices' => array(
                'question 1 -> choice 1',
                'question 1 -> choice 2',
                'question 1 -> choice 3',
                'question 1 -> choice 4',
            ),
            'answer' => array(1),
            'target' => 'course-1',
            'copyId' => $question['id'],
        );
        $this->getQuestionService()->create($copy2);

        $questions = $this->getQuestionService()->findQuestionsByCopyId($question['id']);

        $this->assertEquals(2, count($questions));
    }

    public function testSearch()
    {
        $question1 = $this->createQuestion();
        $question2 = $this->createQuestion1();
        $question3 = $this->createQuestion2();
        $question4 = $this->createMaterialQuestion();

        $conditions = array(
            'type' => 'single_choice',
            'courseSetId' => 1,
        );

        $questions = $this->getQuestionService()->search($conditions, array('createdTime' => 'DESC'), 0, PHP_INT_MAX);
        $this->assertEquals(2, count($questions));

        $questions = $this->getQuestionService()->search(array('parentId' => $question4['id']), array('createdTime' => 'DESC'), 0, PHP_INT_MAX);
        $this->assertEquals(2, count($questions));

        $questions = $this->getQuestionService()->search(array('keyword' => 'fill', 'range' => 'lesson'), array('createdTime' => 'DESC'), 0, PHP_INT_MAX);
        $this->assertEquals(1, count($questions));

        $questions = $this->getQuestionService()->search(array('keyword' => 'determine', 'excludeIds' => $question2['id'].','.$question1['id']), array('createdTime' => 'DESC'), 0, PHP_INT_MAX);
        $this->assertEquals(1, count($questions));
    }

    public function testSearchCount()
    {
        $question1 = $this->createQuestion();
        $question2 = $this->createQuestion1();
        $question3 = $this->createQuestion2();
        $question4 = $this->createMaterialQuestion();

        $conditions = array(
            'types' => array('single_choice', 'determine', 'fill'),
            'courseSetId' => 1,
        );

        $count = $this->getQuestionService()->searchCount($conditions);
        $this->assertEquals(5, $count);
    }

    public function testWaveCount()
    {
        $question = $this->createMaterialQuestion();

        $this->getQuestionService()->waveCount($question['id'], array('subCount' => 1));
        $questionNew = $this->getQuestionService()->get($question['id']);

        $this->assertEquals($question['subCount'] + 1, $questionNew['subCount']);
    }

    public function testJudgeQuestion()
    {
        $result = $this->getQuestionService()->judgeQuestion(array(), array(2));
        $this->assertEquals('notFound', $result['status']);
        $this->assertEquals(0, $result['score']);

        $question = $this->createQuestion();

        $result = $this->getQuestionService()->judgeQuestion($question, array());
        $this->assertEquals('noAnswer', $result['status']);
        $this->assertEquals(0, $result['score']);

        $result = $this->getQuestionService()->judgeQuestion($question, array(2));
        $this->assertEquals('wrong', $result['status']);
        $this->assertEquals(0, $result['score']);

        $result = $this->getQuestionService()->judgeQuestion($question, array(1));
        $this->assertEquals('right', $result['status']);
        $this->assertEquals('2.0', $result['score']);
    }

    public function testHasEssay()
    {
        $question1 = $this->createQuestion();
        $question2 = $this->createQuestion1();
        $question3 = $this->createQuestion2();

        $questionIds = array($question1['id'], $question2['id'], $question3['id']);
        $result = $this->getQuestionService()->hasEssay($questionIds);

        $this->assertFalse($result);

        $question = array(
            'type' => 'essay',
            'stem' => 'essay question stem',
            'content' => 'essay question content',
            'courseId' => 2,
            'courseSetId' => 2,
            'lessonId' => 0,
            'answer' => array('essay question answer'),
            'target' => 'course-2',
        );
        $essay = $this->getQuestionService()->create($question);

        $result = $this->getQuestionService()->hasEssay(array($essay['id']));
        $this->assertTrue($result);
    }

    public function testGetQuestionCountGroupByTypes()
    {
        $question1 = $this->createQuestion();
        $question2 = $this->createQuestion1();
        $question3 = $this->createQuestion2();
        $question4 = $this->createMaterialQuestion();

        $conditions = array(
            'courseSetId' => 1,
        );

        $result = $this->getQuestionService()->getQuestionCountGroupByTypes($conditions);

        $this->assertEquals(4, count($result));
    }

    public function testGetFavoriteQuestion()
    {
        $favorite = $this->createFavorite1();

        $findFavorite = $this->getQuestionService()->getFavoriteQuestion($favorite['id']);

        $this->assertEquals($favorite['targetId'], $findFavorite['targetId']);
        $this->assertEquals($favorite['targetType'], $findFavorite['targetType']);
    }

    public function testCreateFavoriteQuestion()
    {
        $favorite = $this->createFavorite1();

        $findFavorite = $this->getQuestionService()->getFavoriteQuestion($favorite['id']);

        $this->assertEquals($favorite['targetId'], $findFavorite['targetId']);
        $this->assertEquals($favorite['targetType'], $findFavorite['targetType']);
    }

    public function testDeleteFavoriteQuestion()
    {
        $favorite = $this->createFavorite1();

        $findFavorite = $this->getQuestionService()->getFavoriteQuestion($favorite['id']);
        $this->assertEquals($favorite['targetId'], $findFavorite['targetId']);

        $this->getQuestionService()->deleteFavoriteQuestion($favorite['id']);
        $findFavorite = $this->getQuestionService()->getFavoriteQuestion($favorite['id']);

        $this->assertNull($findFavorite);
    }

    public function testSearchFavoriteQuestions()
    {
        $favorite1 = $this->createFavorite1();
        $favorite2 = $this->createFavorite2();
        $favorite3 = $this->createFavorite3();

        $conditions = array(
            'userId' => 1,
        );

        $favorites = $this->getQuestionService()->searchFavoriteQuestions(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            20
        );

        $this->assertEquals(3, count($favorites));
    }

    public function testSearchFavoriteCount()
    {
        $favorite1 = $this->createFavorite1();
        $favorite2 = $this->createFavorite2();
        $favorite3 = $this->createFavorite3();

        $conditions = array(
            'userId' => 1,
        );

        $count = $this->getQuestionService()->searchFavoriteCount($conditions);

        $this->assertEquals(3, $count);
    }

    public function testFindUserFavoriteQuestions()
    {
        $favorite1 = $this->createFavorite1();
        $favorite2 = $this->createFavorite2();
        $favorite3 = $this->createFavorite3();

        $userFavorites = $this->getQuestionService()->findUserFavoriteQuestions(1);

        $this->assertEquals(3, count($userFavorites));
    }

    public function testDeleteFavoriteByQuestionId()
    {
        $favorite = $this->createFavorite1();

        $findFavorite = $this->getQuestionService()->getFavoriteQuestion($favorite['id']);
        $this->assertEquals($favorite['targetId'], $findFavorite['targetId']);

        $this->getQuestionService()->deleteFavoriteByQuestionId($findFavorite['questionId']);

        $favorite = $this->getQuestionService()->getFavoriteQuestion($findFavorite['id']);

        $this->assertNull($favorite);
    }

    public function testFindAttachments()
    {
        $result = $this->getQuestionService()->findAttachments(array());
        $this->assertEmpty($result);

        $this->mockBiz(
            'File:UploadFileService',
            array(
                array(
                    'functionName' => 'searchUseFiles',
                    'returnValue' => array(array('id' => 1, 'targetType' => 'question.stem', 'targetId' => 1), array('id' => 2, 'targetType' => 'question.analysis', 'targetId' => 1)),
                ),
            )
        );

        $results = $this->getQuestionService()->findAttachments(array('1', '2'));

        $this->assertEquals(2, count($results));
        $this->assertArrayHasKey('question.stem1', $results);
        $this->assertArrayHasKey('question.analysis1', $results);
    }

    public function testHasStemImg()
    {
        $question = array(
            'stem' => '123<img src="/file/123.jpg">456',
        );
        $result = $this->getQuestionService()->hasStemImg($question);

        $this->assertTrue($result['includeImg']);
    }

    protected function createQuestion()
    {
        $question = array(
            'type' => 'single_choice',
            'stem' => 'test single choice question 1.',
            'content' => 'question content',
            'courseId' => 1,
            'courseSetId' => 1,
            'lessonId' => 0,
            'choices' => array(
                'question 1 -> choice 1',
                'question 1 -> choice 2',
                'question 1 -> choice 3',
                'question 1 -> choice 4',
            ),
            'answer' => array(1),
            'target' => 'course-1',
            'score' => '2.0',
        );

        return $this->getQuestionService()->create($question);
    }

    protected function createQuestion1()
    {
        $question = array(
            'type' => 'determine',
            'stem' => 'test material-determine question.',
            'content' => 'question determine content',
            'courseId' => 1,
            'courseSetId' => 1,
            'lessonId' => 0,
            'answer' => array(1),
            'target' => 'course-1',
            'metas' => array('mediaId' => 1),
        );

        return $this->getQuestionService()->create($question);
    }

    protected function createQuestion2()
    {
        $question = array(
            'type' => 'fill',
            'stem' => 'fill[[a|b]]',
            'content' => 'question fill content2',
            'courseId' => 1,
            'courseSetId' => 1,
            'lessonId' => 0,
            'answer' => array(array('a', 'b')),
            'target' => 'course-1',
        );

        return $this->getQuestionService()->create($question);
    }

    protected function createQuestion3()
    {
        $question = array(
            'type' => 'fill',
            'stem' => 'fill[[a|b]]',
            'content' => 'question fill content',
            'courseId' => 2,
            'courseSetId' => 2,
            'lessonId' => 0,
            'answer' => array(array('a', 'b')),
            'target' => 'course-2',
        );

        return $this->getQuestionService()->create($question);
    }

    protected function createMaterialQuestion()
    {
        $material = array(
            'type' => 'material',
            'stem' => 'test material question.',
            'content' => 'question material content',
            'courseId' => 1,
            'courseSetId' => 1,
            'lessonId' => 0,
            'answer' => array(),
            'target' => 'course-1',
        );
        $questionParent = $this->getQuestionService()->create($material);

        $single = array(
            'type' => 'single_choice',
            'stem' => 'test material-single choice question.',
            'content' => 'question material-single_choice content',
            'courseId' => 1,
            'courseSetId' => 1,
            'lessonId' => 0,
            'choices' => array(
                'question 1 -> choice 1',
                'question 1 -> choice 2',
                'question 1 -> choice 3',
                'question 1 -> choice 4',
            ),
            'answer' => array(1),
            'target' => 'course-1',
            'parentId' => $questionParent['id'],
        );

        $subQuestion1 = $this->getQuestionService()->create($single);

        $determine = array(
            'type' => 'determine',
            'stem' => 'test material-determine question.',
            'content' => 'question material-determine content',
            'courseId' => 1,
            'courseSetId' => 1,
            'lessonId' => 0,
            'answer' => array(1),
            'target' => 'course-1',
            'parentId' => $questionParent['id'],
            'metas' => array('mediaId' => 1),
        );

        $subQuestion2 = $this->getQuestionService()->create($determine);

        $questionParent = $this->getQuestionService()->get($questionParent['id']);

        return $questionParent;
    }

    protected function createFavorite1()
    {
        $question = $this->createQuestion();

        $fields = array(
            'questionId' => $question['id'],
            'targetType' => 'testpaper',
            'targetId' => 1,
            'target' => 'testpaper-1',
            'userId' => 1,
        );

        return $this->getQuestionService()->createFavoriteQuestion($fields);
    }

    protected function createFavorite2()
    {
        $question = $this->createQuestion1();

        $fields = array(
            'questionId' => $question['id'],
            'targetType' => 'testpaper',
            'targetId' => 1,
            'target' => 'testpaper-1',
            'userId' => 1,
        );

        return $this->getQuestionService()->createFavoriteQuestion($fields);
    }

    protected function createFavorite3()
    {
        $question = $this->createQuestion2();

        $fields = array(
            'questionId' => $question['id'],
            'targetType' => 'testpaper',
            'targetId' => 2,
            'target' => 'testpaper-2',
            'userId' => 1,
        );

        return $this->getQuestionService()->createFavoriteQuestion($fields);
    }

    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }
}
