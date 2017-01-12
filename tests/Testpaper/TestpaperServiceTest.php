<?php

namespace Biz\Testpaper\Tests;

use Biz\BaseTestCase;
use Topxia\Common\ArrayToolkit;

class TestpaperServiceTest extends BaseTestCase
{
    public function testGetTestpaper()
    {
        $testpaper = $this->createTestpaper1();

        $findTestpaper = $this->getTestpaperService()->getTestpaper($testpaper['id']);

        $this->assertEquals($testpaper['type'], $findTestpaper['type']);
    }

    public function testCreateTestpaper()
    {
        $testpaper = $this->createTestpaper1();

        $findTestpaper = $this->getTestpaperService()->getTestpaper($testpaper['id']);

        $this->assertEquals($testpaper['type'], $findTestpaper['type']);
    }

    public function testUpdateTestpaper()
    {
        $testpaper = $this->createTestpaper1();

        $findTestpaper = $this->getTestpaperService()->getTestpaper($testpaper['id']);
        $this->assertEquals($testpaper['name'], $findTestpaper['name']);
        $this->assertEquals($testpaper['description'], $findTestpaper['description']);

        $fields = array(
            'name'        => 'testpaper update',
            'description' => 'testpaper description update'
        );
        $testpaperUpdate = $this->getTestpaperService()->updateTestpaper($findTestpaper['id'], $fields);

        $this->assertEquals($fields['name'], $testpaperUpdate['name']);
        $this->assertEquals($fields['description'], $testpaperUpdate['description']);
    }

    public function testDeleteTestpaper()
    {
        $testpaper = $this->createTestpaper1();

        $findTestpaper = $this->getTestpaperService()->getTestpaper($testpaper['id']);
        $this->assertEquals($testpaper['name'], $findTestpaper['name']);

        $this->getTestpaperService()->deleteTestpaper($testpaper['id']);

        $findTestpaper = $this->getTestpaperService()->getTestpaper($testpaper['id']);

        $this->assertNull($findTestpaper);
    }

    public function testDeleteTestpapers()
    {
        $testpaper = $this->createTestpaper1();
        $homework  = $this->createHomework();
        $exercise  = $this->createExercise();

        $ids = array($testpaper['id'], $homework['id'], $exercise['id']);

        $this->getTestpaperService()->deleteTestpapers($ids);

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaper['id']);
        $homework  = $this->getTestpaperService()->getTestpaper($homework['id']);
        $exercise  = $this->getTestpaperService()->getTestpaper($exercise['id']);

        $this->assertNull($testpaper);
        $this->assertNull($homework);
        $this->assertNull($exercise);
    }

    public function testFindTestpapersByIds()
    {
        $testpaper = $this->createTestpaper1();
        $homework  = $this->createHomework();
        $exercise  = $this->createExercise();

        $ids = array($testpaper['id'], $homework['id']);

        $testpapers = $this->getTestpaperService()->findTestpapersByIds($ids);

        $this->assertEquals(2, count($testpapers));
    }

    public function testSearchTestpapers()
    {
        $testpaper = $this->createTestpaper1();
        $homework  = $this->createHomework();
        $exercise  = $this->createExercise();

        $conditions = array(
            'type' => 'testpaper'
        );
        $testpapers = $this->getTestpaperService()->searchTestpapers(
            $conditions,
            array(),
            0,
            10
        );

        $this->assertEquals(1, count($testpapers));
    }

    public function testSearchTestpaperCount()
    {
        $testpaper = $this->createTestpaper1();
        $homework  = $this->createHomework();
        $exercise  = $this->createExercise();

        $conditions = array(
            'type' => 'testpaper'
        );
        $count = $this->getTestpaperService()->searchTestpaperCount($conditions);

        $this->assertEquals(1, $count);
    }

    public function testPublishTestpaper()
    {
        $testpaper = $this->createTestpaper1();

        $this->assertEquals('draft', $testpaper['status']);

        $testpaper = $this->getTestpaperService()->publishTestpaper($testpaper['id']);
        $this->assertEquals('open', $testpaper['status']);
    }

    public function closeTestpaper($id)
    {
        $testpaper = $this->createTestpaper1();

        $this->assertEquals('draft', $testpaper['status']);

        $testpaper = $this->getTestpaperService()->publishTestpaper($testpaper['id']);
        $this->assertEquals('open', $testpaper['status']);

        $testpaper = $this->getTestpaperService()->closeTestpaper($testpaper['id']);
        $this->assertEquals('closed', $testpaper['status']);
    }

    /**
     * testpaper_item
     */

    public function testGetItem()
    {
        $item = $this->createSingleItem();

        $findItem = $this->getTestpaperService()->getItem($item['id']);

        $this->assertEquals($item['questionType'], $findItem['questionType']);
        $this->assertEquals($item['questionId'], $findItem['questionId']);
    }

    public function testCreateItem()
    {
        $item = $this->createSingleItem();

        $findItem = $this->getTestpaperService()->getItem($item['id']);

        $this->assertEquals($item['questionType'], $findItem['questionType']);
        $this->assertEquals($item['questionId'], $findItem['questionId']);
    }

    public function testUpdateItem()
    {
        $item   = $this->createSingleItem();
        $fields = array(
            'score'     => '4',
            'missScore' => '2'
        );
        $updatedItem = $this->getTestpaperService()->updateItem($item['id'], $fields);

        $this->assertEquals($fields['score'], $updatedItem['score']);
        $this->assertEquals($fields['missScore'], $updatedItem['missScore']);
    }

    public function testDeleteItem()
    {
        $item = $this->createSingleItem();

        $findItem = $this->getTestpaperService()->getItem($item['id']);
        $this->assertEquals($item['questionType'], $findItem['questionType']);

        $this->getTestpaperService()->deleteItem($item['id']);

        $item = $this->getTestpaperService()->getItem($item['id']);

        $this->assertNull($item);
    }

    public function testDeleteItemsByTestId()
    {
        $testpaper = $this->createTestpaper1();
        $items     = $this->createTestpaperItem($testpaper);
        $this->assertEquals(3, count($items));

        $this->getTestpaperService()->deleteItemsByTestId($testpaper['id']);

        $testpaperItems = $this->getTestpaperService()->findItemsByTestId($testpaper['id']);

        $this->assertEmpty($testpaperItems);
    }

    public function testGetItemsCountByParams()
    {
        $testpaper = $this->createTestpaper1();
        $items     = $this->createTestpaperItem($testpaper);
        $itemsTyps = ArrayToolkit::column($items, 'questionType');

        $conditions = array(
            'testId'          => $testpaper['id'],
            'parentIdDefault' => 0
        );
        $result = $this->getTestpaperService()->getItemsCountByParams($conditions, 'questionType');

        $types = ArrayToolkit::column($result, 'questionType');

        $this->assertTrue(in_array('choice', $types));
        $this->assertTrue(in_array('fill', $types));
        $this->assertTrue(in_array('determine', $types));
    }

    public function testFindItemsByTestId()
    {
        $testpaper = $this->createTestpaper1();
        $items     = $this->createTestpaperItem($testpaper);
        $this->assertEquals(3, count($items));

        $testpaperItems = $this->getTestpaperService()->findItemsByTestId($testpaper['id']);

        $this->assertEquals(count($items), count($testpaperItems));
    }

    public function testSearchItems()
    {
        $testpaper = $this->createTestpaper1();
        $items     = $this->createTestpaperItem($testpaper);

        $conditions = array(
            'testId' => $testpaper['id']
        );
        $testpaperItems = $this->getTestpaperService()->searchItems($conditions, array('id' => 'DESC'), 0, 10);
        $this->assertEquals(count($items), count($testpaperItems));
    }

    public function searchItemCount($conditions)
    {
        $testpaper = $this->createTestpaper1();
        $items     = $this->createTestpaperItem($testpaper);

        $conditions = array(
            'testId' => $testpaper['id']
        );
        $count = $this->getTestpaperService()->searchItems($conditions);

        $this->assertEquals(count($items), $count);
    }

    /*
     * testpaper_item_result
     */

    public function testCreateItemResult()
    {
        $item = $this->createSingleItem();

        $fields = array(
            'itemId'     => $item['id'],
            'testId'     => $item['testId'],
            'resultId'   => 1,
            'userId'     => 1,
            'questionId' => $item['questionId'],
            'answer'     => array(1),
            'status'     => 'wrong',
            'score'      => 0
        );

        $itemResult = $this->getTestpaperService()->createItemResult($fields);

        $this->assertEquals($fields['status'], $itemResult['status']);
        $this->assertEquals($fields['itemId'], $itemResult['itemId']);
    }

    public function testUpdateItemResult()
    {
        $item = $this->createSingleItem();

        $fields = array(
            'itemId'     => $item['id'],
            'testId'     => $item['testId'],
            'resultId'   => 1,
            'userId'     => 1,
            'questionId' => $item['questionId'],
            'answer'     => array(1),
            'status'     => 'wrong',
            'score'      => 0
        );
        $itemResult = $this->getTestpaperService()->createItemResult($fields);
        $this->assertEquals($fields['status'], $itemResult['status']);
        $this->assertEquals($fields['itemId'], $itemResult['itemId']);

        $updateFields = array(
            'answer' => array(1),
            'status' => 'right',
            'score'  => 1
        );
        $update = $this->getTestpaperService()->updateItemResult($itemResult['id'], $updateFields);

        $this->assertEquals($updateFields['status'], $update['status']);
        $this->assertEquals($updateFields['score'], $update['score']);
        $this->assertArrayEquals($updateFields['answer'], $update['answer']);
    }

    public function testFindItemResultsByResultId()
    {
        $item = $this->createSingleItem();

        $fields = array(
            'itemId'     => $item['id'],
            'testId'     => $item['testId'],
            'resultId'   => 1,
            'userId'     => 1,
            'questionId' => $item['questionId'],
            'answer'     => array(1),
            'status'     => 'wrong',
            'score'      => 0
        );
        $itemResult = $this->getTestpaperService()->createItemResult($fields);

        $itemResults = $this->getTestpaperService()->findItemResultsByResultId(1);

        $this->assertEquals(1, count($itemResults));
    }

    /**
     * testpaper_result
     */

    public function testGetTestpaperResult()
    {
        $testpaper       = $this->createTestpaper1();
        $testpaperResult = $this->createTestpaperResult1($testpaper);

        $result = $this->getTestpaperService()->getTestpaperResult($testpaperResult['id']);

        $this->assertEquals($testpaper['id'], $result['testId']);
        $this->assertEquals($testpaper['name'], $result['paperName']);
    }

    public function testGetUserUnfinishResult()
    {
        $testpaper = $this->createTestpaper1();

        $result = $this->getTestpaperService()->getUserUnfinishResult($testpaper['id'], 1, 1, $testpaper['type'], 1);
        $this->assertNull($result);

        $paperResult = $this->createTestpaperResult1($testpaper);

        $result = $this->getTestpaperService()->getUserUnfinishResult($testpaper['id'], 1, 1, $testpaper['type'], 1);

        $this->assertNotNull($result);
    }

    public function testGetUserLatelyResultByTestId()
    {
        $testpaper = $this->createTestpaper1();

        $paperResult1 = $this->createTestpaperResult3($testpaper);
        $paperResult2 = $this->createTestpaperResult1($testpaper);

        $result = $this->getTestpaperService()->getUserLatelyResultByTestId(1, $testpaper['id'], $testpaper['courseSetId'], 1, $testpaper['type']);

        $this->assertEquals($paperResult2['status'], $result['status']);
    }

    public function findPaperResultsStatusNumGroupByStatus($testId)
    {
        $testpaper = $this->createTestpaper1();

        $paperResult1 = $this->createTestpaperResult1($testpaper);
        $paperResult2 = $this->createTestpaperResult2($testpaper);
        $paperResult3 = $this->createTestpaperResult3($testpaper);

        $result = $this->getTestpaperService()->findPaperResultsStatusNumGroupByStatus($testpaper['id']);

        $this->assertEquals(2, $result['doing']);
        $this->assertEquals(1, $result['finished']);
    }

    public function testAddTestpaperResult()
    {
        $testpaper       = $this->createTestpaper1();
        $testpaperResult = $this->createTestpaperResult1($testpaper);

        $result = $this->getTestpaperService()->getTestpaperResult($testpaperResult['id']);

        $this->assertEquals($testpaper['id'], $result['testId']);
        $this->assertEquals($testpaper['name'], $result['paperName']);
    }

    public function testUpdateTestpaperResult()
    {
        $testpaper       = $this->createTestpaper1();
        $testpaperResult = $this->createTestpaperResult1($testpaper);

        $fields = array(
            'score'          => '5',
            'objectiveScore' => 5,
            'usedTime'       => 5,
            'endTime'        => time(),
            'rightItemCount' => 1,
            'status'         => 'reviewing'
        );
        $result = $this->getTestpaperService()->updateTestpaperResult($testpaperResult['id'], $fields);

        $this->assertEquals($fields['status'], $result['status']);
        $this->assertEquals($fields['score'], $result['score']);
        $this->assertEquals($fields['rightItemCount'], $result['rightItemCount']);
    }

    public function testSearchTestpaperResultsCount()
    {
        $testpaper        = $this->createTestpaper1();
        $testpaperResult1 = $this->createTestpaperResult1($testpaper);
        $testpaperResult2 = $this->createTestpaperResult2($testpaper);
        $testpaperResult3 = $this->createTestpaperResult3($testpaper);

        $conditions = array(
            'testId' => $testpaper['id'],
            'status' => 'doing'
        );
        $count = $this->getTestpaperService()->searchTestpaperResultsCount($conditions);
        $this->assertEquals(2, $count);

        $conditions = array(
            'userId' => 1
        );
        $count = $this->getTestpaperService()->searchTestpaperResultsCount($conditions);
        $this->assertEquals(2, $count);
    }

    public function testSearchTestpaperResults()
    {
        $testpaper        = $this->createTestpaper1();
        $testpaperResult1 = $this->createTestpaperResult1($testpaper);
        $testpaperResult2 = $this->createTestpaperResult2($testpaper);
        $testpaperResult3 = $this->createTestpaperResult3($testpaper);

        $conditions = array(
            'testId' => $testpaper['id'],
            'status' => 'doing'
        );
        $results = $this->getTestpaperService()->searchTestpaperResults($conditions, array('endTime' => 'DESC'), 0, 10);

        $this->assertEquals(2, count($results));
    }

    public function testSearchTestpapersScore()
    {
        $testpaper        = $this->createTestpaper1();
        $testpaperResult1 = $this->createTestpaperResult1($testpaper);
        $testpaperResult2 = $this->createTestpaperResult2($testpaper);
        $testpaperResult3 = $this->createTestpaperResult3($testpaper);

        $conditions = array(
            'testId' => $testpaper['id']
        );
        $score = $this->getTestpaperService()->searchTestpapersScore($conditions);

        $this->assertEquals($testpaperResult3['score'], $score);
    }

    public function testBuildTestpaper()
    {
        $choiceQuestions    = $this->generateChoiceQuestions(1, 2);
        $fillQuestions      = $this->generateFillQuestions(1, 2);
        $determineQuestions = $this->generateDetermineQuestions(1, 2);
        $essayQuestions     = $this->generateEssayQuestions(1, 2);

        $fields1 = array(
            'name'        => 'testpaper',
            'description' => 'testpaper description',
            'mode'        => 'rand',
            'ranges'      => array('course'),
            'counts'      => array('choice' => 2, 'fill' => 2, 'determine' => 1),
            'scores'      => array('choice' => 2, 'fill' => 2, 'determine' => 2),
            'missScores'  => array('choice' => 1, 'uncertain_choice' => 1),
            'courseSetId' => 1,
            'courseId'    => 0,
            'pattern'     => 'questionType',
            'type'        => 'testpaper'
        );
        $testpaper = $this->getTestpaperService()->buildTestpaper($fields1, 'testpaper');

        $this->assertEquals($fields1['type'], $testpaper['type']);
        $this->assertArrayEquals($fields1['counts'], $testpaper['metas']['counts']);

        $fields2 = array(
            'name'        => 'homework',
            'description' => 'homework description',
            'itemCount'   => 3,
            'questionIds' => array($choiceQuestions[0]['id'], $fillQuestions[0]['id'], $determineQuestions[0]['id']),
            'courseSetId' => 1,
            'courseId'    => 0,
            'pattern'     => 'questionType',
            'type'        => 'homework'
        );
        $homework = $this->getTestpaperService()->buildTestpaper($fields2, 'homework');
        $this->assertEquals($fields2['type'], $homework['type']);
        $this->assertEquals($fields2['description'], $homework['description']);
        $this->assertEquals($fields2['itemCount'], $homework['itemCount']);

        $fields3 = array(
            'name'          => 'exercise',
            'description'   => 'exercise description',
            'itemCount'     => 3,
            'questionTypes' => array('choice', 'fill', 'determine', 'essay'),
            'difficulty'    => 'normal',
            'range'         => 'course',
            'courseSetId'   => 1,
            'courseId'      => 0,
            'pattern'       => 'questionType',
            'type'          => 'exercise'
        );
        $exercise = $this->getTestpaperService()->buildTestpaper($fields3, 'exercise');
        $this->assertEquals($fields3['type'], $exercise['type']);
        $this->assertEquals($fields3['itemCount'], $exercise['itemCount']);

        $this->assertArrayEquals($fields3['questionTypes'], $exercise['metas']['questionTypes']);
    }

    public function testCanBuildTestpaper()
    {
        $choiceQuestions    = $this->generateChoiceQuestions(1, 2);
        $fillQuestions      = $this->generateFillQuestions(1, 2);
        $determineQuestions = $this->generateDetermineQuestions(1, 2);
        $essayQuestions     = $this->generateEssayQuestions(1, 2);

        $options1 = array(
            'mode'       => 'range',
            'ranges'     => array('course'),
            'counts'     => array('choice' => 2, 'fill' => 2, 'determine' => 1),
            'scores'     => array('choice' => 2, 'fill' => 2, 'determine' => 2),
            'missScores' => array('choice' => 1, 'uncertain_choice' => 1),
            'courseId'   => 1
        );
        $result = $this->getTestpaperService()->canBuildTestpaper('testpaper', $options1);

        $this->assertEquals('yes', $result['status']);

        $options2 = array(
            'itemCount'     => 3,
            'questionTypes' => array('choice', 'fill', 'determine', 'essay'),
            'difficulty'    => 'normal',
            'range'         => 'course',
            'courseId'      => 1
        );
        $result = $this->getTestpaperService()->canBuildTestpaper('exercise', $options2);
        $this->assertEquals('yes', $result['status']);
    }

    public function testStartTestpaper()
    {
        $testpaper = $this->createTestpaper1();

        $fields = array(
            'lessonId' => 1,
            'courseId' => 1
        );
        $testpaperResult = $this->getTestpaperService()->startTestpaper($testpaper['id'], $fields);

        $this->assertNotNull($testpaperResult);
        $this->assertEquals($testpaper['id'], $testpaperResult['testId']);
    }

    public function testFinishTest()
    {
        $choiceQuestions    = $this->generateChoiceQuestions(1, 2);
        $fillQuestions      = $this->generateFillQuestions(1, 2);
        $determineQuestions = $this->generateDetermineQuestions(1, 2);
        $essayQuestions     = $this->generateEssayQuestions(1, 2);

        $fields1 = array(
            'name'        => 'testpaper',
            'description' => 'testpaper description',
            'mode'        => 'range',
            'ranges'      => array('course'),
            'counts'      => array('choice' => 2, 'fill' => 2, 'determine' => 1),
            'scores'      => array('choice' => 2, 'fill' => 2, 'determine' => 2),
            'missScores'  => array('choice' => 1, 'uncertain_choice' => 1),
            'courseSetId' => 1,
            'courseId'    => 0,
            'pattern'     => 'questionType',
            'type'        => 'testpaper'
        );
        $testpaper = $this->getTestpaperService()->buildTestpaper($fields1, 'testpaper');
        $fields    = array(
            'lessonId' => 1,
            'courseId' => 1
        );
        $testpaperResult = $this->getTestpaperService()->startTestpaper($testpaper['id'], $fields);

        $formData = array(
            'usedTime' => 5,
            'data'     => array(
                $choiceQuestions[0]['id'] => array(2, 3),
                $fillQuestions[0]['id']   => array('fill answer')
            )
        );
        $result = $this->getTestpaperService()->finishTest($testpaperResult['id'], $formData);

        $this->assertEquals('finished', $result['status']);
    }

    public function testShowTestpaperItems()
    {
        $choiceQuestions    = $this->generateChoiceQuestions(1, 2);
        $fillQuestions      = $this->generateFillQuestions(1, 2);
        $determineQuestions = $this->generateDetermineQuestions(1, 2);
        $essayQuestions     = $this->generateEssayQuestions(1, 2);

        $fields1 = array(
            'name'        => 'testpaper',
            'description' => 'testpaper description',
            'mode'        => 'range',
            'ranges'      => array('course'),
            'counts'      => array('choice' => 2, 'fill' => 2, 'determine' => 1),
            'scores'      => array('choice' => 2, 'fill' => 2, 'determine' => 2),
            'missScores'  => array('choice' => 1, 'uncertain_choice' => 1),
            'courseSetId' => 1,
            'courseId'    => 0,
            'pattern'     => 'questionType',
            'type'        => 'testpaper'
        );
        $testpaper = $this->getTestpaperService()->buildTestpaper($fields1, 'testpaper');
        $items     = $this->getTestpaperService()->showTestpaperItems($testpaper['id']);
        $this->assertArrayEquals(array_keys($fields1['counts']), array_keys($items));

        $fields2 = array(
            'name'        => 'homework',
            'description' => 'homework description',
            'itemCount'   => 3,
            'questionIds' => array($choiceQuestions[0]['id'], $fillQuestions[0]['id'], $determineQuestions[0]['id']),
            'courseSetId' => 1,
            'courseId'    => 0,
            'pattern'     => 'questionType',
            'type'        => 'homework'
        );
        $homework = $this->getTestpaperService()->buildTestpaper($fields2, 'homework');
        $items    = $this->getTestpaperService()->showTestpaperItems($homework['id']);
        $this->assertEquals($fields2['itemCount'], count($items));

        $fields3 = array(
            'name'          => 'exercise',
            'description'   => 'exercise description',
            'itemCount'     => 3,
            'questionTypes' => array('choice', 'fill', 'determine', 'essay'),
            'difficulty'    => 'normal',
            'range'         => 'course',
            'courseSetId'   => 1,
            'courseId'      => 0,
            'pattern'       => 'questionType',
            'type'          => 'exercise'
        );
        $exercise = $this->getTestpaperService()->buildTestpaper($fields3, 'exercise');
        $items    = $this->getTestpaperService()->showTestpaperItems($exercise['id']);
        $this->assertEquals($fields3['itemCount'], count($items));
    }

    public function testMakeAccuracy()
    {
        $choiceQuestions    = $this->generateChoiceQuestions(1, 2);
        $fillQuestions      = $this->generateFillQuestions(1, 2);
        $determineQuestions = $this->generateDetermineQuestions(1, 2);
        $essayQuestions     = $this->generateEssayQuestions(1, 2);

        $fields1 = array(
            'name'        => 'testpaper',
            'description' => 'testpaper description',
            'mode'        => 'range',
            'ranges'      => array('course'),
            'counts'      => array('choice' => 2, 'fill' => 2, 'determine' => 1),
            'scores'      => array('choice' => 2, 'fill' => 2, 'determine' => 2),
            'missScores'  => array('choice' => 1, 'uncertain_choice' => 1),
            'courseSetId' => 1,
            'courseId'    => 0,
            'pattern'     => 'questionType',
            'type'        => 'testpaper'
        );
        $testpaper = $this->getTestpaperService()->buildTestpaper($fields1, 'testpaper');
        $fields    = array(
            'lessonId' => 1,
            'courseId' => 1
        );
        $testpaperResult = $this->getTestpaperService()->startTestpaper($testpaper['id'], $fields);

        $formData = array(
            'usedTime' => 5,
            'data'     => array(
                $choiceQuestions[0]['id'] => array(2, 3),
                $fillQuestions[0]['id']   => array('fill answer')
            )
        );
        $result = $this->getTestpaperService()->finishTest($testpaperResult['id'], $formData);

        $accuracy = $this->getTestpaperService()->makeAccuracy($result['id']);

        $this->assertArrayEquals(array_keys($fields1['counts']), array_keys($accuracy));
    }

    public function testCheckFinish()
    {
        $choiceQuestions    = $this->generateChoiceQuestions(1, 2);
        $fillQuestions      = $this->generateFillQuestions(1, 2);
        $determineQuestions = $this->generateDetermineQuestions(1, 2);
        $essayQuestions     = $this->generateEssayQuestions(1, 1);

        $fields1 = array(
            'name'        => 'testpaper',
            'description' => 'testpaper description',
            'mode'        => 'range',
            'ranges'      => array('course'),
            'counts'      => array('choice' => 2, 'fill' => 2, 'essay' => 1),
            'scores'      => array('choice' => 2, 'fill' => 2, 'essay' => 2),
            'missScores'  => array('choice' => 1, 'uncertain_choice' => 1),
            'courseSetId' => 1,
            'courseId'    => 0,
            'pattern'     => 'questionType',
            'type'        => 'testpaper'
        );
        $testpaper = $this->getTestpaperService()->buildTestpaper($fields1, 'testpaper');
        $fields    = array(
            'lessonId' => 1,
            'courseId' => 1
        );
        $testpaperResult = $this->getTestpaperService()->startTestpaper($testpaper['id'], $fields);
        $this->assertEquals('doing', $testpaperResult['status']);

        $formData = array(
            'usedTime' => 5,
            'data'     => array(
                $choiceQuestions[0]['id'] => array(2, 3),
                $fillQuestions[0]['id']   => array('fill answer')
            )
        );
        $result = $this->getTestpaperService()->finishTest($testpaperResult['id'], $formData);
        $this->assertEquals('reviewing', $result['status']);

        $fields = array(
            'result'       => array(
                $essayQuestions[0]['id'] => array(
                    'score'      => 1,
                    'teacherSay' => 'question check teacher say'
                )
            ),
            'teacherSay'   => 'teacher say content',
            'passedStatus' => 'passed'
        );
        $result = $this->getTestpaperService()->checkFinish($testpaperResult['id'], $fields);

        $this->assertEquals('finished', $result['status']);
        $this->assertEquals(1, $result['subjectiveScore']);
    }

    public function testSubmitAnswers()
    {
        $choiceQuestions    = $this->generateChoiceQuestions(1, 2);
        $fillQuestions      = $this->generateFillQuestions(1, 2);
        $determineQuestions = $this->generateDetermineQuestions(1, 2);
        $essayQuestions     = $this->generateEssayQuestions(1, 1);

        $fields1 = array(
            'name'        => 'testpaper',
            'description' => 'testpaper description',
            'mode'        => 'range',
            'ranges'      => array('course'),
            'counts'      => array('choice' => 2, 'fill' => 2),
            'scores'      => array('choice' => 2, 'fill' => 2),
            'missScores'  => array('choice' => 1, 'uncertain_choice' => 1),
            'courseSetId' => 1,
            'courseId'    => 0,
            'pattern'     => 'questionType',
            'type'        => 'testpaper'
        );
        $testpaper = $this->getTestpaperService()->buildTestpaper($fields1, 'testpaper');
        $fields    = array(
            'lessonId' => 1,
            'courseId' => 1
        );
        $testpaperResult = $this->getTestpaperService()->startTestpaper($testpaper['id'], $fields);

        $answers = array(
            $choiceQuestions[0]['id'] => array(2, 3),
            $fillQuestions[0]['id']   => array('fill answer')
        );
        $itemResults = $this->getTestpaperService()->submitAnswers($testpaperResult['id'], $answers);

        $this->assertEquals(2, count($itemResults));
    }

    public function testSumScore()
    {
        $choiceQuestions    = $this->generateChoiceQuestions(1, 2);
        $fillQuestions      = $this->generateFillQuestions(1, 2);
        $determineQuestions = $this->generateDetermineQuestions(1, 2);
        $essayQuestions     = $this->generateEssayQuestions(1, 1);

        $fields1 = array(
            'name'        => 'testpaper',
            'description' => 'testpaper description',
            'mode'        => 'range',
            'ranges'      => array('course'),
            'counts'      => array('choice' => 2, 'fill' => 2),
            'scores'      => array('choice' => 2, 'fill' => 2),
            'missScores'  => array('choice' => 1, 'uncertain_choice' => 1),
            'courseSetId' => 1,
            'courseId'    => 0,
            'pattern'     => 'questionType',
            'type'        => 'testpaper'
        );
        $testpaper = $this->getTestpaperService()->buildTestpaper($fields1, 'testpaper');
        $fields    = array(
            'lessonId' => 1,
            'courseId' => 1
        );
        $testpaperResult = $this->getTestpaperService()->startTestpaper($testpaper['id'], $fields);
        $this->assertEquals('doing', $testpaperResult['status']);

        $formData = array(
            'usedTime' => 5,
            'data'     => array(
                $choiceQuestions[0]['id'] => array(2),
                $fillQuestions[0]['id']   => array('fill answer')
            )
        );
        $result      = $this->getTestpaperService()->finishTest($testpaperResult['id'], $formData);
        $itemResults = $this->getTestpaperService()->findItemResultsByResultId($result['id']);

        $scoreResult = $this->getTestpaperService()->sumScore($itemResults);
        $this->assertEquals(1, $scoreResult['sumScore']);
        $this->assertEquals(0, $scoreResult['rightItemCount']);
    }

    public function testFindAttachments()
    {
        $testpaper   = $this->createTestpaper1();
        $attachments = $this->getTestpaperService()->findAttachments($testpaper['id']);

        $this->assertEmpty($attachments);
    }

    //public function canLookTestpaper($resultId);

    public function testUpdateTestpaperItems()
    {
        $choiceQuestions    = $this->generateChoiceQuestions(1, 4);
        $fillQuestions      = $this->generateFillQuestions(1, 2);
        $determineQuestions = $this->generateDetermineQuestions(1, 2);
        $essayQuestions     = $this->generateEssayQuestions(1, 1);

        $fields1 = array(
            'name'        => 'testpaper',
            'description' => 'testpaper description',
            'mode'        => 'range',
            'ranges'      => array('course'),
            'counts'      => array('choice' => 1, 'fill' => 1),
            'scores'      => array('choice' => 2, 'fill' => 2),
            'missScores'  => array('choice' => 1, 'uncertain_choice' => 1),
            'courseSetId' => 1,
            'courseId'    => 0,
            'pattern'     => 'questionType',
            'type'        => 'testpaper'
        );
        $testpaper = $this->getTestpaperService()->buildTestpaper($fields1, 'testpaper');

        $items = array(
            'questions' => array(
                array('id' => $choiceQuestions[0]['id'], 'score' => 4, 'missScores' => 2, 'type' => 'choice'),
                array('id' => $choiceQuestions[1]['id'], 'score' => 4, 'missScores' => 2, 'type' => 'choice'),
                array('id' => $choiceQuestions[2]['id'], 'score' => 4, 'missScores' => 2, 'type' => 'choice')
            )
        );
        $testpaper = $this->getTestpaperService()->updateTestpaperItems($testpaper['id'], $items);

        $this->assertEquals(count($items['questions']), $testpaper['itemCount']);
    }

    protected function createTestpaper1()
    {
        $fields = array(
            'name'        => 'testpaper',
            'description' => 'testpaper description',
            'courseSetId' => 1,
            'courseId'    => 0,
            'pattern'     => 'questionType',
            'metas'       => array(
                'ranges' => array('course')
            ),
            'type'        => 'testpaper'
        );
        return $this->getTestpaperService()->createTestpaper($fields);
    }

    protected function createHomework()
    {
        $fields = array(
            'name'        => 'homework',
            'description' => 'homework description',
            'courseSetId' => 1,
            'courseId'    => 1,
            'pattern'     => 'questionType',
            'metas'       => array(),
            'type'        => 'homework',
            'status'      => 'open'
        );
        return $this->getTestpaperService()->createTestpaper($fields);
    }

    protected function createExercise()
    {
        $fields = array(
            'name'            => 'exercise',
            'description'     => 'exercise description',
            'itemCount'       => 1,
            'courseSetId'     => 1,
            'courseId'        => 1,
            'pattern'         => 'questionType',
            'metas'           => array(
                'questionTypes' => array('choice', 'single_choice', 'fill', 'determine'),
                'difficulty'    => 'normal',
                'range'         => 'course'
            ),
            'type'            => 'exercise',
            'status'          => 'open',
            'passedCondition' => array(0)
        );
        return $this->getTestpaperService()->createTestpaper($fields);
    }

    protected function createTestpaperItem($testpaper)
    {
        $choiceQuestions    = $this->generateChoiceQuestions($testpaper['courseSetId'], 1);
        $fillQuestions      = $this->generateFillQuestions($testpaper['courseSetId'], 1);
        $determineQuestions = $this->generateDetermineQuestions($testpaper['courseSetId'], 1);

        $questions = array_merge($choiceQuestions, $fillQuestions, $determineQuestions);

        $items = array();
        $seq   = 1;
        foreach ($questions as $question) {
            $fields = array(
                'testId'       => $testpaper['id'],
                'seq'          => $seq,
                'questionId'   => $question['id'],
                'questionType' => $question['type'],
                'parentId'     => $question['parentId'],
                'score'        => $question['score'],
                'missScore'    => 0
            );
            $items[] = $this->getTestpaperService()->createItem($fields);
            $seq++;
        }

        return $items;
    }

    protected function createSingleItem()
    {
        $fields = array(
            'testId'       => 1,
            'seq'          => 1,
            'questionId'   => 1,
            'questionType' => 'choice',
            'parentId'     => 0,
            'score'        => '2',
            'missScore'    => 0
        );
        return $this->getTestpaperService()->createItem($fields);
    }

    protected function createTestpaperResult1($testpaper)
    {
        $fields = array(
            'paperName'   => $testpaper['name'],
            'testId'      => $testpaper['id'],
            'userId'      => 1,
            'limitedTime' => $testpaper['limitedTime'],
            'beginTime'   => time(),
            'status'      => 'doing',
            'usedTime'    => 0,
            'courseId'    => 1,
            'courseSetId' => $testpaper['courseSetId'],
            'lessonId'    => 1,
            'type'        => $testpaper['type']
        );

        return $this->getTestpaperService()->addTestpaperResult($fields);
    }

    protected function createTestpaperResult2($testpaper)
    {
        $fields = array(
            'paperName'   => $testpaper['name'],
            'testId'      => $testpaper['id'],
            'userId'      => 2,
            'limitedTime' => $testpaper['limitedTime'],
            'beginTime'   => time(),
            'status'      => 'doing',
            'usedTime'    => 0,
            'courseId'    => 1,
            'courseSetId' => $testpaper['courseSetId'],
            'lessonId'    => 1,
            'type'        => $testpaper['type']
        );

        return $this->getTestpaperService()->addTestpaperResult($fields);
    }

    protected function createTestpaperResult3($testpaper)
    {
        $fields = array(
            'paperName'   => $testpaper['name'],
            'testId'      => $testpaper['id'],
            'userId'      => 1,
            'limitedTime' => $testpaper['limitedTime'],
            'score'       => '5',
            'endTime'     => time(),
            'beginTime'   => time(),
            'status'      => 'finished',
            'usedTime'    => 0,
            'courseId'    => 1,
            'courseSetId' => $testpaper['courseSetId'],
            'lessonId'    => 1,
            'type'        => $testpaper['type']
        );

        return $this->getTestpaperService()->addTestpaperResult($fields);
    }

    protected function generateChoiceQuestions($courseId, $count, $difficulty = null)
    {
        $questions = array();
        for ($i = 0; $i < $count; $i++) {
            $question = array(
                'type'       => 'choice',
                'stem'       => 'test single choice question.',
                'choices'    => array(
                    'question -> choice 1',
                    'question -> choice 2',
                    'question -> choice 3',
                    'question -> choice 4'
                ),
                'answer'     => array(1, 2),
                'courseId'   => $courseId,
                'target'     => 'course/'.$courseId,
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty
            );

            $questions[] = $this->getQuestionService()->create($question);
        }
        return $questions;
    }

    protected function generateFillQuestions($courseId, $count, $difficulty = null)
    {
        $questions = array();
        for ($i = 0; $i < $count; $i++) {
            $question = array(
                'type'       => 'fill',
                'stem'       => 'fill question [[aaa]].',
                'target'     => 'course/'.$courseId,
                'courseId'   => $courseId,
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty
            );

            $questions[] = $this->getQuestionService()->create($question);
        }
        return $questions;
    }

    protected function generateDetermineQuestions($courseId, $count, $difficulty = null)
    {
        $questions = array();
        for ($i = 0; $i < $count; $i++) {
            $question = array(
                'type'       => 'determine',
                'stem'       => 'determine question.',
                'target'     => 'course/'.$courseId,
                'courseId'   => $courseId,
                'answer'     => array(0),
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty
            );

            $questions[] = $this->getQuestionService()->create($question);
        }
        return $questions;
    }

    protected function generateEssayQuestions($courseId, $count, $difficulty = null)
    {
        $questions = array();
        for ($i = 0; $i < $count; $i++) {
            $question = array(
                'type'       => 'essay',
                'stem'       => 'essay question.',
                'target'     => 'course/'.$courseId,
                'courseId'   => $courseId,
                'answer'     => array('xxx'),
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty
            );

            $questions[] = $this->getQuestionService()->create($question);
        }
        return $questions;
    }

    protected function generateMaterialQuestions($courseId, $count, $difficulty = null)
    {
        $questions = array();
        for ($i = 0; $i < $count; $i++) {
            $question = array(
                'type'       => 'material',
                'stem'       => 'material question.',
                'target'     => 'course/'.$courseId,
                'courseId'   => $courseId,
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty
            );

            $questions[] = $this->getQuestionService()->create($question);
        }
        return $questions;
    }

    protected function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question:QuestionService');
    }

    protected function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Testpaper:TestpaperService');
    }
}
