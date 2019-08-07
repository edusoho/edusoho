<?php

namespace Tests\Unit\Testpaper\Service;

use Biz\BaseTestCase;
use AppBundle\Common\ArrayToolkit;
use Biz\Testpaper\Service\TestpaperService;
use Biz\User\CurrentUser;

class TestpaperServiceTest extends BaseTestCase
{
    public function testGetTestpaper()
    {
        $testpaper = $this->createTestpaper1();

        $findTestpaper = $this->getTestpaperService()->getTestpaper($testpaper['id']);

        $this->assertEquals($testpaper['type'], $findTestpaper['type']);
    }

    public function testGetTestpaperByIdAndType()
    {
        $testpaper = $this->createTestpaper1();
        $result = $this->getTestpaperService()->getTestpaperByIdAndType($testpaper['id'], 'testpaper');

        $this->assertArrayEquals($testpaper, $result);
    }

    public function testFindTestpapersByIdsAndType()
    {
        $testpaper1 = $this->createTestpaper1();
        $testpaper2 = $this->createHomework();

        $results = $this->getTestpaperService()->findTestpapersByIdsAndType(array($testpaper1['id'], $testpaper2['id']), 'testpaper');

        $this->assertEquals(1, count($results));
        $this->assertArrayEquals($testpaper1, $results[0]);
    }

    public function testGetTestpaperByCopyIdAndCourseSetId()
    {
        $testpaper = $this->createTestpaper1();
        $copyTestpaper1 = $testpaper;
        unset($copyTestpaper1['id']);
        $copyTestpaper1['copyId'] = $testpaper['id'];
        $copyTestpaper1['courseSetId'] = 2;
        $copyTestpaper1 = $this->getTestpaperService()->createTestpaper($copyTestpaper1);

        $copyTestpaper2 = $testpaper;
        unset($copyTestpaper2['id']);
        $copyTestpaper2['copyId'] = $testpaper['id'];
        $copyTestpaper2['courseSetId'] = 3;
        $copyTestpaper2 = $this->getTestpaperService()->createTestpaper($copyTestpaper2);

        $copyTestpaper = $this->getTestpaperService()->getTestpaperByCopyIdAndCourseSetId($testpaper['id'], 2);

        $this->assertArrayEquals($copyTestpaper1, $copyTestpaper);
    }

    public function testCreateTestpaper()
    {
        $testpaper = $this->createTestpaper1();

        $findTestpaper = $this->getTestpaperService()->getTestpaper($testpaper['id']);

        $this->assertEquals($testpaper['type'], $findTestpaper['type']);
    }

    public function testBatchCreateTestpaper()
    {
        $result = $this->getTestpaperService()->batchCreateTestpaper(array());
        $this->assertNull($result);

        $testpapers = array(
            array(
                'name' => 'testpaper1',
                'description' => 'testpaper description',
                'courseSetId' => 1,
                'courseId' => 0,
                'pattern' => 'questionType',
                'metas' => array(
                    'ranges' => array('courseId' => 0),
                ),
                'type' => 'testpaper',
            ),
            array(
                'name' => 'testpaper2',
                'description' => 'testpaper2 description',
                'courseSetId' => 1,
                'courseId' => 0,
                'pattern' => 'questionType',
                'metas' => array(
                    'ranges' => array('courseId' => 0),
                ),
                'type' => 'testpaper',
            ),
        );

        $result = $this->getTestpaperService()->batchCreateTestpaper($testpapers);
        $this->assertTrue($result);
    }

    /**
     * @expectedException \Biz\Testpaper\TestpaperException
     */
    public function testUpdateTestpaper()
    {
        $result = $this->getTestpaperService()->updateTestpaper('123', array());

        $testpaper = $this->createTestpaper1();

        $findTestpaper = $this->getTestpaperService()->getTestpaper($testpaper['id']);
        $this->assertEquals($testpaper['name'], $findTestpaper['name']);
        $this->assertEquals($testpaper['description'], $findTestpaper['description']);

        $fields = array(
            'name' => 'testpaper update',
            'description' => 'testpaper description update',
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

    /**
     * @expectedException \Biz\Testpaper\TestpaperException
     */
    public function testDeleteEmptyTestpaper()
    {
        $result = $this->getTestpaperService()->deleteTestpaper(123, true);
        $this->assertEmpty($result);

        $this->getTestpaperService()->deleteTestpaper(123);
    }

    public function testDeleteTestpapers()
    {
        $result = $this->getTestpaperService()->deleteTestpapers(array());
        $this->assertFalse($result);

        $testpaper = $this->createTestpaper1();
        $homework = $this->createHomework();
        $exercise = $this->createExercise();

        $ids = array($testpaper['id'], $homework['id'], $exercise['id']);

        $result = $this->getTestpaperService()->deleteTestpapers($ids);

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaper['id']);
        $homework = $this->getTestpaperService()->getTestpaper($homework['id']);
        $exercise = $this->getTestpaperService()->getTestpaper($exercise['id']);

        $this->assertNull($testpaper);
        $this->assertNull($homework);
        $this->assertNull($exercise);
        $this->assertTrue($result);
    }

    public function testFindTestpapersByIds()
    {
        $testpaper = $this->createTestpaper1();
        $homework = $this->createHomework();
        $exercise = $this->createExercise();

        $ids = array($testpaper['id'], $homework['id']);

        $testpapers = $this->getTestpaperService()->findTestpapersByIds($ids);

        $this->assertEquals(2, count($testpapers));
    }

    public function testSearchTestpapers()
    {
        $testpaper = $this->createTestpaper1();
        $homework = $this->createHomework();
        $exercise = $this->createExercise();

        $conditions = array(
            'type' => 'testpaper',
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
        $homework = $this->createHomework();
        $exercise = $this->createExercise();

        $conditions = array(
            'type' => 'testpaper',
        );
        $count = $this->getTestpaperService()->searchTestpaperCount($conditions);

        $this->assertEquals(1, $count);
    }

    /**
     * @expectedException \Biz\Testpaper\TestpaperException
     * @expectedExceptionMessage exception.testpaper.not_found
     */
    public function testPublishTestpaperEmpty()
    {
        $this->getTestpaperService()->publishTestpaper(123);
    }

    public function testPublishTestpaper()
    {
        $testpaper = $this->createTestpaper1();

        $this->assertEquals('draft', $testpaper['status']);

        $testpaper = $this->getTestpaperService()->publishTestpaper($testpaper['id']);
        $this->assertEquals('open', $testpaper['status']);
    }

    /**
     * @expectedException \Biz\Testpaper\TestpaperException
     * @expectedExceptionMessage exception.testpaper.status_invalid
     */
    public function testPublishTestpaperStatus()
    {
        $fields = array(
            'name' => 'testpaper',
            'description' => 'testpaper description',
            'courseSetId' => 1,
            'courseId' => 0,
            'pattern' => 'questionType',
            'metas' => array(
                'ranges' => array('courseId' => 0),
            ),
            'type' => 'testpaper',
            'status' => 'published',
        );
        $testpaper = $this->getTestpaperService()->createTestpaper($fields);
        $this->getTestpaperService()->publishTestpaper($testpaper['id']);
    }

    /**
     * @expectedException \Biz\Testpaper\TestpaperException
     * @expectedExceptionMessage exception.testpaper.not_found
     */
    public function testCloseTestpaperEmpty()
    {
        $this->getTestpaperService()->closeTestpaper(123);
    }

    public function testCloseTestpaper()
    {
        $testpaper = $this->createTestpaper1();

        $this->assertEquals('draft', $testpaper['status']);

        $testpaper = $this->getTestpaperService()->publishTestpaper($testpaper['id']);
        $this->assertEquals('open', $testpaper['status']);

        $testpaper = $this->getTestpaperService()->closeTestpaper($testpaper['id']);
        $this->assertEquals('closed', $testpaper['status']);
    }

    /**
     * @expectedException \Biz\Testpaper\TestpaperException
     * @expectedExceptionMessage exception.testpaper.status_invalid
     */
    public function testCloseTestpaperStatus()
    {
        $fields = array(
            'name' => 'testpaper',
            'description' => 'testpaper description',
            'courseSetId' => 1,
            'courseId' => 0,
            'pattern' => 'questionType',
            'metas' => array(
                'ranges' => array('courseId' => 0),
            ),
            'type' => 'testpaper',
            'status' => 'closed',
        );
        $testpaper = $this->getTestpaperService()->createTestpaper($fields);
        $this->getTestpaperService()->closeTestpaper($testpaper['id']);
    }

    /**
     * testpaper_item.
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

    public function testBatchCreateItems()
    {
        $result = $this->getTestpaperService()->batchCreateItems(array());
        $this->assertEmpty($result);

        $testpaper = $this->createTestpaper1();

        $total = 4100;
        for ($i = 1; $i <= $total; ++$i) {
            $newItems[] = array(
                'testId' => $testpaper['id'],
                'seq' => $i,
                'questionId' => $i,
                'questionType' => 'single_choice',
                'parentId' => 0,
                'score' => '2.0',
                'missScore' => 0,
                'copyId' => 0,
                'type' => 'testpaper',
            );
        }
        $result = $this->getTestpaperService()->batchCreateItems($newItems);

        $results = $this->getTestpaperService()->findItemsByTestId($testpaper['id']);
        $this->assertEquals($total, count($results));
    }

    public function testUpdateItem()
    {
        $item = $this->createSingleItem();
        $fields = array(
            'score' => '4',
            'missScore' => '2',
        );
        $updatedItem = $this->getTestpaperService()->updateItem($item['id'], $fields);

        $this->assertEquals((int) $fields['score'], (int) $updatedItem['score']);
        $this->assertEquals((int) $fields['missScore'], (int) $updatedItem['missScore']);
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
        $items = $this->createTestpaperItem($testpaper);
        $this->assertEquals(3, count($items));

        $this->getTestpaperService()->deleteItemsByTestId($testpaper['id']);

        $testpaperItems = $this->getTestpaperService()->findItemsByTestId($testpaper['id']);

        $this->assertEmpty($testpaperItems);
    }

    public function testGetItemsCountByParams()
    {
        $testpaper = $this->createTestpaper1();
        $items = $this->createTestpaperItem($testpaper);
        $itemsTyps = ArrayToolkit::column($items, 'questionType');

        $conditions = array(
            'testId' => $testpaper['id'],
            'parentIdDefault' => 0,
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
        $items = $this->createTestpaperItem($testpaper);
        $this->assertEquals(3, count($items));

        $testpaperItems = $this->getTestpaperService()->findItemsByTestId($testpaper['id']);

        $this->assertEquals(count($items), count($testpaperItems));
    }

    public function testFindItemsByTestIds()
    {
        $testpaper = $this->createTestpaper1();
        $this->createTestpaperItem($testpaper);

        $fields = array(
            'name' => 'testpaper',
            'description' => 'testpaper description',
            'courseSetId' => 1,
            'courseId' => 0,
            'pattern' => 'questionType',
            'metas' => array(
                'ranges' => array('courseId' => 0),
            ),
            'type' => 'testpaper',
        );
        $testpaper2 = $this->getTestpaperService()->createTestpaper($fields);
        $this->createTestpaperItem($testpaper2);

        $testpaperItems = $this->getTestpaperService()->findItemsByTestIds(array($testpaper['id'], $testpaper2['id']));

        $this->assertEquals(6, count($testpaperItems));
    }

    public function testSearchItems()
    {
        $testpaper = $this->createTestpaper1();
        $items = $this->createTestpaperItem($testpaper);

        $conditions = array(
            'testId' => $testpaper['id'],
        );
        $testpaperItems = $this->getTestpaperService()->searchItems($conditions, array('id' => 'DESC'), 0, 10);
        $this->assertEquals(count($items), count($testpaperItems));
    }

    public function searchItemCount($conditions)
    {
        $testpaper = $this->createTestpaper1();
        $items = $this->createTestpaperItem($testpaper);

        $conditions = array(
            'testId' => $testpaper['id'],
        );
        $count = $this->getTestpaperService()->searchItems($conditions);

        $this->assertEquals(count($items), $count);
    }

    /*
     * testpaper_item_result
     */

    public function testGetItemResult()
    {
        $fields = array(
            'itemId' => 1,
            'testId' => 1,
            'resultId' => 1,
            'userId' => 1,
            'questionId' => 123,
            'answer' => array(1),
            'status' => 'wrong',
            'score' => 0,
        );
        $item = $this->getTestpaperService()->createItemResult($fields);

        $result = $this->getTestpaperService()->getItemResult($item['id']);
        $this->assertArrayEquals($item, $result);
    }

    public function testCreateItemResult()
    {
        $item = $this->createSingleItem();

        $fields = array(
            'itemId' => $item['id'],
            'testId' => $item['testId'],
            'resultId' => 1,
            'userId' => 1,
            'questionId' => $item['questionId'],
            'answer' => array(1),
            'status' => 'wrong',
            'score' => 0,
        );

        $itemResult = $this->getTestpaperService()->createItemResult($fields);

        $this->assertEquals($fields['status'], $itemResult['status']);
        $this->assertEquals($fields['itemId'], $itemResult['itemId']);
    }

    public function testUpdateItemResult()
    {
        $item = $this->createSingleItem();

        $fields = array(
            'itemId' => $item['id'],
            'testId' => $item['testId'],
            'resultId' => 1,
            'userId' => 1,
            'questionId' => $item['questionId'],
            'answer' => array(1),
            'status' => 'wrong',
            'score' => 0,
        );
        $itemResult = $this->getTestpaperService()->createItemResult($fields);
        $this->assertEquals($fields['status'], $itemResult['status']);
        $this->assertEquals($fields['itemId'], $itemResult['itemId']);

        $updateFields = array(
            'answer' => array(1),
            'status' => 'right',
            'score' => 1,
        );
        $update = $this->getTestpaperService()->updateItemResult($itemResult['id'], $updateFields);

        $this->assertEquals($updateFields['status'], $update['status']);
        $this->assertEquals($updateFields['score'], $update['score']);
        $this->assertArrayEquals($updateFields['answer'], $update['answer']);
    }

    public function testFindItemResultsByResultId()
    {
        $choiceQuestions = $this->generateChoiceQuestions(1, 2);
        $fillQuestions = $this->generateFillQuestions(1, 2);
        $determineQuestions = $this->generateDetermineQuestions(1, 2);
        $essayQuestions = $this->generateEssayQuestions(1, 2);

        $fields1 = array(
            'name' => 'testpaper',
            'description' => 'testpaper description',
            'mode' => 'range',
            'ranges' => array('courseId' => 0),
            'passedScore' => 60,
            'counts' => array('choice' => 2, 'fill' => 2, 'determine' => 1),
            'scores' => array('choice' => 2, 'fill' => 2, 'determine' => 2),
            'missScores' => array('choice' => 1, 'uncertain_choice' => 1),
            'courseSetId' => 1,
            'courseId' => 0,
            'pattern' => 'questionType',
            'type' => 'testpaper',
        );
        $testpaper = $this->getTestpaperService()->buildTestpaper($fields1, 'testpaper');
        $fields = array(
            'lessonId' => 1,
            'courseId' => 1,
        );
        $testpaperResult = $this->getTestpaperService()->startTestpaper($testpaper['id'], $fields);

        $answers = array(
            $choiceQuestions[0]['id'] => array(2, 3),
            $fillQuestions[0]['id'] => array('fill answer'),
        );
        $formData = array(
            'usedTime' => 5,
            'data' => json_encode($answers),
            'attachments' => array(),
        );

        $result = $this->getTestpaperService()->finishTest($testpaperResult['id'], $formData);

        $itemResults = $this->getTestpaperService()->findItemResultsByResultId($result['id']);
        $this->assertEquals(2, count($itemResults));
        $this->assertArrayNotHasKey('attachment', $itemResults[0]);

        $this->mockBiz('File:UploadFileService', array(
            array(
                'functionName' => 'searchUseFiles',
                'returnValue' => array(array('id' => 1, 'targetId' => $itemResults[0]['id']), array('id' => 2, 'targetId' => 2)),
            ),
        ));
        $itemResults = $this->getTestpaperService()->findItemResultsByResultId($result['id'], true);
        $this->assertEquals(2, count($itemResults));
        $this->assertArrayHasKey('attachment', $itemResults[0]);

        $testpaperResult = $this->getTestpaperService()->startTestpaper($testpaper['id'], $fields);
        $itemResults = $this->getTestpaperService()->findItemResultsByResultId($testpaperResult['id'], true);
        $this->assertEquals(0, count($itemResults));
    }

    /**
     * testpaper_result.
     */
    public function testGetTestpaperResult()
    {
        $testpaper = $this->createTestpaper1();
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

    public function testGetUserFinishedResult()
    {
        $testpaper = $this->createTestpaper1();

        $result = $this->getTestpaperService()->getUserFinishedResult($testpaper['id'], 1, 1, $testpaper['type'], 1);
        $this->assertNull($result);

        $paperResult = $this->createTestpaperResult1($testpaper);

        $result = $this->getTestpaperService()->getUserFinishedResult($testpaper['id'], 1, 1, $testpaper['type'], 1);

        $this->assertNull($result);
    }

    public function testGetUserLatelyResultByTestId()
    {
        $testpaper = $this->createTestpaper1();

        $paperResult1 = $this->createTestpaperResult3($testpaper);
        $paperResult2 = $this->createTestpaperResult1($testpaper);

        $result = $this->getTestpaperService()->getUserLatelyResultByTestId(1, $testpaper['id'], $testpaper['courseSetId'], 1, $testpaper['type']);

        $this->assertEquals($paperResult2['status'], $result['status']);
    }

    public function testFindPaperResultsStatusNumGroupByStatus()
    {
        $result = $this->getTestpaperService()->findPaperResultsStatusNumGroupByStatus(1, 1);
        $this->assertEmpty($result);

        $testpaper = $this->createTestpaper1();

        $paperResult1 = $this->createTestpaperResult1($testpaper);
        $paperResult2 = $this->createTestpaperResult2($testpaper);
        $paperResult3 = $this->createTestpaperResult3($testpaper);

        $courseIds = array(1);
        $result = $this->getTestpaperService()->findPaperResultsStatusNumGroupByStatus($testpaper['id'], 1);

        $this->assertEquals(2, $result['doing']);
        $this->assertEquals(1, $result['finished']);
    }

    public function testAddTestpaperResult()
    {
        $testpaper = $this->createTestpaper1();
        $testpaperResult = $this->createTestpaperResult1($testpaper);

        $result = $this->getTestpaperService()->getTestpaperResult($testpaperResult['id']);

        $this->assertEquals($testpaper['id'], $result['testId']);
        $this->assertEquals($testpaper['name'], $result['paperName']);
    }

    public function testUpdateTestpaperResult()
    {
        $testpaper = $this->createTestpaper1();
        $testpaperResult = $this->createTestpaperResult1($testpaper);

        $fields = array(
            'score' => '5',
            'objectiveScore' => 5,
            'usedTime' => 5,
            'endTime' => time(),
            'rightItemCount' => 1,
            'status' => 'reviewing',
        );
        $result = $this->getTestpaperService()->updateTestpaperResult($testpaperResult['id'], $fields);

        $this->assertEquals($fields['status'], $result['status']);
        $this->assertEquals((int) $fields['score'], (int) $result['score']);
        $this->assertEquals($fields['rightItemCount'], $result['rightItemCount']);
    }

    public function testSearchTestpaperResultsCount()
    {
        $result = $this->getTestpaperService()->searchTestpaperResultsCount(array('courseIds' => array()));
        $this->assertEmpty($result);

        $testpaper = $this->createTestpaper1();
        $testpaperResult1 = $this->createTestpaperResult1($testpaper);
        $testpaperResult2 = $this->createTestpaperResult2($testpaper);
        $testpaperResult3 = $this->createTestpaperResult3($testpaper);

        $conditions = array(
            'testId' => $testpaper['id'],
            'status' => 'doing',
        );
        $count = $this->getTestpaperService()->searchTestpaperResultsCount($conditions);
        $this->assertEquals(2, $count);

        $conditions = array(
            'userId' => 1,
        );
        $count = $this->getTestpaperService()->searchTestpaperResultsCount($conditions);
        $this->assertEquals(2, $count);
    }

    public function testSearchTestpaperResults()
    {
        $results = $this->getTestpaperService()->searchTestpaperResults(array('courseIds' => array()), array('endTime' => 'DESC'), 0, 10);
        $this->assertEmpty($results);

        $testpaper = $this->createTestpaper1();
        $testpaperResult1 = $this->createTestpaperResult1($testpaper);
        $testpaperResult2 = $this->createTestpaperResult2($testpaper);
        $testpaperResult3 = $this->createTestpaperResult3($testpaper);

        $conditions = array(
            'testId' => $testpaper['id'],
            'status' => 'doing',
        );
        $results = $this->getTestpaperService()->searchTestpaperResults($conditions, array('endTime' => 'DESC'), 0, 10);

        $this->assertEquals(2, count($results));
    }

    public function testSearchTestpapersScore()
    {
        $testpaper = $this->createTestpaper1();
        $testpaperResult1 = $this->createTestpaperResult1($testpaper);
        $testpaperResult2 = $this->createTestpaperResult2($testpaper);
        $testpaperResult3 = $this->createTestpaperResult3($testpaper);

        $conditions = array(
            'testId' => $testpaper['id'],
        );
        $score = $this->getTestpaperService()->searchTestpapersScore($conditions);

        $this->assertEquals($testpaperResult3['score'], $score);
    }

    public function testBuildTestpaper()
    {
        $choiceQuestions = $this->generateChoiceQuestions(1, 2);
        $fillQuestions = $this->generateFillQuestions(1, 2);
        $determineQuestions = $this->generateDetermineQuestions(1, 2);
        $essayQuestions = $this->generateEssayQuestions(1, 2);

        $fields1 = array(
            'name' => 'testpaper',
            'description' => 'testpaper description',
            'mode' => 'rand',
            'ranges' => array('courseId' => 0),
            'counts' => array('choice' => 2, 'fill' => 2, 'determine' => 1),
            'scores' => array('choice' => 2, 'fill' => 2, 'determine' => 2),
            'missScores' => array('choice' => 1, 'uncertain_choice' => 1),
            'courseSetId' => 1,
            'courseId' => 0,
            'pattern' => 'questionType',
            'type' => 'testpaper',
        );
        $testpaper = $this->getTestpaperService()->buildTestpaper($fields1, 'testpaper');

        $this->assertEquals($fields1['type'], $testpaper['type']);
        $this->assertArrayEquals($fields1['counts'], $testpaper['metas']['counts']);

        $fields2 = array(
            'name' => 'homework',
            'description' => 'homework description',
            'itemCount' => 3,
            'questionIds' => array($choiceQuestions[0]['id'], $fillQuestions[0]['id'], $determineQuestions[0]['id']),
            'courseSetId' => 1,
            'courseId' => 0,
            'pattern' => 'questionType',
            'type' => 'homework',
        );
        $homework = $this->getTestpaperService()->buildTestpaper($fields2, 'homework');
        $this->assertEquals($fields2['type'], $homework['type']);
        $this->assertEquals($fields2['description'], $homework['description']);
        $this->assertEquals($fields2['itemCount'], $homework['itemCount']);

        $fields3 = array(
            'name' => 'exercise',
            'description' => 'exercise description',
            'itemCount' => 3,
            'questionTypes' => array('choice', 'fill', 'determine', 'essay'),
            'difficulty' => 'normal',
            'range' => 'course',
            'courseSetId' => 1,
            'courseId' => 0,
            'pattern' => 'questionType',
            'type' => 'exercise',
        );
        $exercise = $this->getTestpaperService()->buildTestpaper($fields3, 'exercise');
        $this->assertEquals($fields3['type'], $exercise['type']);
        $this->assertEquals($fields3['itemCount'], $exercise['itemCount']);

        $this->assertArrayEquals($fields3['questionTypes'], $exercise['metas']['questionTypes']);
    }

    public function testCanBuildTestpaper()
    {
        $choiceQuestions = $this->generateChoiceQuestions(1, 2);
        $fillQuestions = $this->generateFillQuestions(1, 2);
        $determineQuestions = $this->generateDetermineQuestions(1, 2);
        $essayQuestions = $this->generateEssayQuestions(1, 2);

        $options1 = array(
            'mode' => 'range',
            'ranges' => array('courseId' => 0),
            'counts' => array('choice' => 2, 'fill' => 2, 'determine' => 1),
            'scores' => array('choice' => 2, 'fill' => 2, 'determine' => 2),
            'missScores' => array('choice' => 1, 'uncertain_choice' => 1),
            'courseSetId' => 1,
        );
        $result = $this->getTestpaperService()->canBuildTestpaper('testpaper', $options1);

        $this->assertEquals('yes', $result['status']);

        $options2 = array(
            'itemCount' => 3,
            'questionTypes' => array('choice', 'fill', 'determine', 'essay'),
            'difficulty' => 'normal',
            'range' => 'course',
            'courseSetId' => 1,
        );
        $result = $this->getTestpaperService()->canBuildTestpaper('exercise', $options2);
        $this->assertEquals('yes', $result['status']);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testStartTestpaper()
    {
        $testpaper = $this->createTestpaper1();

        $this->getTestpaperService()->startTestpaper($testpaper['id'], array());

        $fields = array(
            'lessonId' => 1,
            'courseId' => 1,
        );
        $testpaperResult = $this->getTestpaperService()->startTestpaper($testpaper['id'], $fields);

        $this->assertNotNull($testpaperResult);
        $this->assertEquals($testpaper['id'], $testpaperResult['testId']);
    }

    /**
     * @expectedException \Biz\Testpaper\TestpaperException
     */
    public function testFinishTest()
    {
        $choiceQuestions = $this->generateChoiceQuestions(1, 2);
        $fillQuestions = $this->generateFillQuestions(1, 2);
        $determineQuestions = $this->generateDetermineQuestions(1, 2);
        $essayQuestions = $this->generateEssayQuestions(1, 2);

        $fields1 = array(
            'name' => 'testpaper',
            'description' => 'testpaper description',
            'mode' => 'range',
            'ranges' => array('courseId' => 0),
            'passedScore' => 60,
            'counts' => array('choice' => 2, 'fill' => 2, 'determine' => 1),
            'scores' => array('choice' => 2, 'fill' => 2, 'determine' => 2),
            'missScores' => array('choice' => 1, 'uncertain_choice' => 1),
            'courseSetId' => 1,
            'courseId' => 0,
            'pattern' => 'questionType',
            'type' => 'testpaper',
        );
        $testpaper = $this->getTestpaperService()->buildTestpaper($fields1, 'testpaper');
        $fields = array(
            'lessonId' => 1,
            'courseId' => 1,
        );
        $testpaperResult = $this->getTestpaperService()->startTestpaper($testpaper['id'], $fields);

        $answers = array(
            $choiceQuestions[0]['id'] => array(2, 3),
            $fillQuestions[0]['id'] => array('fill answer'),
        );
        $formData = array(
            'usedTime' => 5,
            'data' => json_encode($answers),
            'attachments' => array(),
        );

        $result = $this->getTestpaperService()->finishTest($testpaperResult['id'], $formData);

        $this->assertEquals('finished', $result['status']);

        $this->getTestpaperService()->finishTest($testpaperResult['id'], $formData);
    }

    /**
     * @expectedException \Biz\Testpaper\TestpaperException
     * @expectedExceptionMessage exception.testpaper.forbidden_access_testpaper
     */
    public function testFinishTestUserError()
    {
        $fields = array(
            'paperName' => 'paper name',
            'testId' => 1,
            'userId' => 2,
            'limitedTime' => 0,
            'beginTime' => time(),
            'status' => 'doing',
            'usedTime' => 0,
            'courseId' => 1,
            'courseSetId' => 1,
            'lessonId' => 1,
            'type' => 'testpaper',
        );
        $result = $this->getTestpaperService()->addTestpaperResult($fields);

        $this->getTestpaperService()->finishTest($result['id'], array());
    }

    public function testCountQuestionTypes()
    {
        $result = $this->getTestpaperService()->countQuestionTypes(array('type' => 'homework'), array());
        $this->assertEmpty($result);

        $testpaper = array(
            'type' => 'testpaper',
            'metas' => array('counts' => array('single_choice' => 1, 'fill' => 0, 'material' => 1)),
        );
        $items = array(
            'single_choice' => array(array('score' => '2.0', 'missScore' => 0)),
            'material' => array(array('subs' => array('score' => '5.0', 'missScore' => 0))),
        );

        $total = $this->getTestpaperService()->countQuestionTypes($testpaper, $items);

        $this->assertEquals(1, $total['single_choice']['number']);
        $this->assertEquals(1, $total['material']['number']);
        $this->assertEquals(0, $total['single_choice']['missScore']);
        $this->assertEquals(0, $total['material']['missScore']);
    }

    public function testShowTestpaperItems()
    {
        $choiceQuestions = $this->generateChoiceQuestions(1, 2);
        $fillQuestions = $this->generateFillQuestions(1, 2);
        $determineQuestions = $this->generateDetermineQuestions(1, 2);
        $essayQuestions = $this->generateEssayQuestions(1, 2);

        $fields1 = array(
            'name' => 'testpaper',
            'description' => 'testpaper description',
            'mode' => 'range',
            'ranges' => array('courseId' => 0),
            'counts' => array('choice' => 2, 'fill' => 2, 'determine' => 1),
            'scores' => array('choice' => 2, 'fill' => 2, 'determine' => 2),
            'missScores' => array('choice' => 1, 'uncertain_choice' => 1),
            'courseSetId' => 1,
            'courseId' => 0,
            'pattern' => 'questionType',
            'type' => 'testpaper',
        );
        $testpaper = $this->getTestpaperService()->buildTestpaper($fields1, 'testpaper');
        $items = $this->getTestpaperService()->showTestpaperItems($testpaper['id']);
        $this->assertArrayEquals(array_keys($fields1['counts']), array_keys($items));

        $fields2 = array(
            'name' => 'homework',
            'description' => 'homework description',
            'itemCount' => 3,
            'questionIds' => array($choiceQuestions[0]['id'], $fillQuestions[0]['id'], $determineQuestions[0]['id']),
            'courseSetId' => 1,
            'courseId' => 0,
            'pattern' => 'questionType',
            'type' => 'homework',
        );
        $homework = $this->getTestpaperService()->buildTestpaper($fields2, 'homework');
        $items = $this->getTestpaperService()->showTestpaperItems($homework['id']);
        $this->assertEquals($fields2['itemCount'], count($items));

        $fields3 = array(
            'name' => 'exercise',
            'description' => 'exercise description',
            'itemCount' => 3,
            'questionTypes' => array('choice', 'fill', 'determine', 'essay'),
            'difficulty' => 'normal',
            'range' => 'course',
            'courseSetId' => 1,
            'courseId' => 0,
            'pattern' => 'questionType',
            'type' => 'exercise',
        );
        $exercise = $this->getTestpaperService()->buildTestpaper($fields3, 'exercise');
        $items = $this->getTestpaperService()->showTestpaperItems($exercise['id']);
        $this->assertEquals($fields3['itemCount'], count($items));
    }

    public function testMakeAccuracy()
    {
        $choiceQuestions = $this->generateChoiceQuestions(1, 2);
        $fillQuestions = $this->generateFillQuestions(1, 2);
        $determineQuestions = $this->generateDetermineQuestions(1, 1);
        $essayQuestions = $this->generateEssayQuestions(1, 2);
        $materialQuestions = $this->generateMaterialQuestions(1, 1);
        $question = array(
            'type' => 'choice',
            'stem' => 'test single choice question.',
            'choices' => array(
                'question -> choice 1',
                'question -> choice 2',
                'question -> choice 3',
                'question -> choice 4',
            ),
            'answer' => array(1, 2),
            'courseSetId' => 1,
            'target' => 'course/1',
            'parentId' => $materialQuestions[0]['id'],
        );
        $this->getQuestionService()->create($question);

        $fields1 = array(
            'name' => 'testpaper',
            'description' => 'testpaper description',
            'mode' => 'range',
            'ranges' => array('courseId' => 0),
            'counts' => array('choice' => 2, 'fill' => 2, 'determine' => 1, 'material' => 1),
            'scores' => array('choice' => 2, 'fill' => 2, 'determine' => 2, 'material' => 5),
            'missScores' => array('choice' => 1, 'uncertain_choice' => 1),
            'courseSetId' => 1,
            'courseId' => 0,
            'passedScore' => 60,
            'pattern' => 'questionType',
            'type' => 'testpaper',
        );
        $testpaper = $this->getTestpaperService()->buildTestpaper($fields1, 'testpaper');
        $fields = array(
            'lessonId' => 1,
            'courseId' => 1,
        );
        $testpaperResult = $this->getTestpaperService()->startTestpaper($testpaper['id'], $fields);

        $answers = array(
            $choiceQuestions[0]['id'] => array(2),
            $fillQuestions[0]['id'] => array('fill answer'),
            $determineQuestions[0]['id'] => array(0),
        );
        $formData = array(
            'usedTime' => 5,
            'data' => json_encode($answers),
            'attachments' => array(),
        );

        $result = $this->getTestpaperService()->finishTest($testpaperResult['id'], $formData);
        $accuracy = $this->getTestpaperService()->makeAccuracy($result['id']);

        $this->assertArrayEquals(array_keys($fields1['counts']), array_keys($accuracy));
        $this->assertEquals(1, $accuracy['choice']['partRight']);
        $this->assertEquals(1, $accuracy['choice']['noAnswer']);
        $this->assertEquals(1, $accuracy['choice']['score']);
        $this->assertEquals(2, $accuracy['choice']['all']);

        $this->assertEquals(1, $accuracy['fill']['wrong']);
        $this->assertEquals(1, $accuracy['fill']['noAnswer']);
        $this->assertEquals(0, $accuracy['fill']['score']);
        $this->assertEquals(2, $accuracy['fill']['all']);

        $this->assertEquals(1, $accuracy['determine']['right']);
        $this->assertEquals(2, $accuracy['determine']['score']);
        $this->assertEquals(1, $accuracy['determine']['all']);

        $this->assertEquals(1, $accuracy['material']['noAnswer']);
        $this->assertEquals(0, $accuracy['material']['score']);
        $this->assertEquals(1, $accuracy['material']['all']);
    }

    public function testCheckFinish()
    {
        $choiceQuestions = $this->generateChoiceQuestions(1, 2);
        $fillQuestions = $this->generateFillQuestions(1, 2);
        $determineQuestions = $this->generateDetermineQuestions(1, 2);
        $essayQuestions = $this->generateEssayQuestions(1, 1);

        $fields1 = array(
            'name' => 'testpaper',
            'description' => 'testpaper description',
            'mode' => 'range',
            'ranges' => array('courseId' => 0),
            'counts' => array('choice' => 2, 'fill' => 2, 'essay' => 1),
            'scores' => array('choice' => 2, 'fill' => 2, 'essay' => 2),
            'missScores' => array('choice' => 1, 'uncertain_choice' => 1),
            'courseSetId' => 1,
            'courseId' => 0,
            'passedScore' => 60,
            'pattern' => 'questionType',
            'type' => 'testpaper',
        );
        $testpaper = $this->getTestpaperService()->buildTestpaper($fields1, 'testpaper');
        $fields = array(
            'lessonId' => 1,
            'courseId' => 1,
        );
        $testpaperResult = $this->getTestpaperService()->startTestpaper($testpaper['id'], $fields);
        $this->assertEquals('doing', $testpaperResult['status']);

        $answers = array(
            $choiceQuestions[0]['id'] => array(2, 3),
            $essayQuestions[0]['id'] => array('essay answer'),
        );
        $formData = array(
            'usedTime' => 5,
            'data' => json_encode($answers),
            'attachments' => array(),
        );

        $result = $this->getTestpaperService()->finishTest($testpaperResult['id'], $formData);
        $this->assertEquals('reviewing', $result['status']);

        $fields = array(
            'result' => array(
                $essayQuestions[0]['id'] => array(
                    'score' => 1,
                    'teacherSay' => 'question check teacher say',
                ),
            ),
            'teacherSay' => 'teacher say content',
            'passedStatus' => 'passed',
        );
        $result = $this->getTestpaperService()->checkFinish($testpaperResult['id'], $fields);

        $this->assertEquals('finished', $result['status']);
        $this->assertEquals(1, $result['subjectiveScore']);
    }

    public function testCheckHomeworkFinish()
    {
        $choiceQuestions = $this->generateChoiceQuestions(1, 2);
        $fillQuestions = $this->generateFillQuestions(1, 1);
        $determineQuestions = $this->generateDetermineQuestions(1, 1);
        $essayQuestions = $this->generateEssayQuestions(1, 1);

        $fields2 = array(
            'name' => 'homework',
            'description' => 'homework description',
            'itemCount' => 3,
            'questionIds' => array($choiceQuestions[0]['id'], $fillQuestions[0]['id'], $determineQuestions[0]['id'], $essayQuestions[0]['id']),
            'courseSetId' => 1,
            'courseId' => 0,
            'pattern' => 'questionType',
            'type' => 'homework',
        );
        $homework = $this->getTestpaperService()->buildTestpaper($fields2, 'homework');

        $fields = array(
            'lessonId' => 1,
            'courseId' => 1,
        );
        $testpaperResult = $this->getTestpaperService()->startTestpaper($homework['id'], $fields);
        $this->assertEquals('doing', $testpaperResult['status']);

        $answers = array(
            $choiceQuestions[0]['id'] => array(2, 3),
            $essayQuestions[0]['id'] => array('essay answer'),
        );
        $formData = array(
            'usedTime' => 5,
            'data' => json_encode($answers),
            'attachments' => array(),
        );

        $result = $this->getTestpaperService()->finishTest($testpaperResult['id'], $formData);
        $this->assertEquals('reviewing', $result['status']);

        $fields = array(
            'result' => array(
                $essayQuestions[0]['id'] => array(
                    'teacherSay' => 'question check teacher say',
                ),
            ),
            'teacherSay' => 'teacher say content',
            'passedStatus' => 'passed',
        );
        $result = $this->getTestpaperService()->checkFinish($testpaperResult['id'], $fields);

        $this->assertEquals('finished', $result['status']);
        $this->assertEquals(0, $result['subjectiveScore']);
    }

    public function testSubmitAnswers()
    {
        $choiceQuestions = $this->generateChoiceQuestions(1, 2);
        $fillQuestions = $this->generateFillQuestions(1, 2);
        $determineQuestions = $this->generateDetermineQuestions(1, 2);
        $essayQuestions = $this->generateEssayQuestions(1, 1);

        $fields1 = array(
            'name' => 'testpaper',
            'description' => 'testpaper description',
            'mode' => 'range',
            'ranges' => array('courseId' => 0),
            'counts' => array('choice' => 2, 'fill' => 2),
            'scores' => array('choice' => 2, 'fill' => 2),
            'missScores' => array('choice' => 1, 'uncertain_choice' => 1),
            'courseSetId' => 1,
            'courseId' => 0,
            'pattern' => 'questionType',
            'type' => 'testpaper',
        );
        $testpaper = $this->getTestpaperService()->buildTestpaper($fields1, 'testpaper');
        $fields = array(
            'lessonId' => 1,
            'courseId' => 1,
        );
        $testpaperResult = $this->getTestpaperService()->startTestpaper($testpaper['id'], $fields);

        $alreadyItemResult = array(
            'itemId' => 10,
            'testId' => $testpaper['id'],
            'resultId' => 1,
            'userId' => 1,
            'questionId' => $fillQuestions[0]['id'],
            'answer' => array(1),
            'status' => 'wrong',
            'score' => 0,
        );
        $this->getTestpaperService()->createItemResult($alreadyItemResult);

        $itemResults = $this->getTestpaperService()->submitAnswers($testpaperResult['id'], array(), array());
        $this->assertEmpty($itemResults);

        $answers = array(
            $choiceQuestions[0]['id'] => array(2, 3),
            $fillQuestions[0]['id'] => array('fill answer'),
            123 => array(1),
        );

        $answers = json_encode($answers);

        $this->mockBiz('File:UploadFileService', array(
            array(
                'functionName' => 'createUseFiles',
                'returnValue' => array(),
            ),
        ));
        $itemResults = $this->getTestpaperService()->submitAnswers($testpaperResult['id'], $answers, array($choiceQuestions[0]['id'] => array(1, 2)));

        $this->assertEquals(3, count($itemResults));
    }

    /**
     * @expectedException \Exception
     */
    public function testSubmitAnswersException()
    {
        $choiceQuestions = $this->generateChoiceQuestions(1, 2);
        $fillQuestions = $this->generateFillQuestions(1, 2);
        $determineQuestions = $this->generateDetermineQuestions(1, 2);
        $essayQuestions = $this->generateEssayQuestions(1, 1);

        $fields1 = array(
            'name' => 'testpaper',
            'description' => 'testpaper description',
            'mode' => 'range',
            'ranges' => array('courseId' => 0),
            'counts' => array('choice' => 2, 'fill' => 2),
            'scores' => array('choice' => 2, 'fill' => 2),
            'missScores' => array('choice' => 1, 'uncertain_choice' => 1),
            'courseSetId' => 1,
            'courseId' => 0,
            'pattern' => 'questionType',
            'type' => 'testpaper',
        );
        $testpaper = $this->getTestpaperService()->buildTestpaper($fields1, 'testpaper');
        $fields = array(
            'lessonId' => 1,
            'courseId' => 1,
        );
        $testpaperResult = $this->getTestpaperService()->startTestpaper($testpaper['id'], $fields);

        $answers = array(
            $choiceQuestions[0]['id'] => array(2, 3),
            $fillQuestions[0]['id'] => array('fill answer'),
            123 => array(1),
        );

        $answers = json_encode($answers);

        $this->mockBiz('File:UploadFileService', array(
            array(
                'functionName' => 'createUseFiles',
                'throwException' => new \Exception(),
            ),
        ));
        $itemResults = $this->getTestpaperService()->submitAnswers($testpaperResult['id'], $answers, array($choiceQuestions[0]['id'] => array(1, 2)));
    }

    public function testSumScore()
    {
        $choiceQuestions = $this->generateChoiceQuestions(1, 2);
        $fillQuestions = $this->generateFillQuestions(1, 2);
        $determineQuestions = $this->generateDetermineQuestions(1, 2);
        $essayQuestions = $this->generateEssayQuestions(1, 1);

        $fields1 = array(
            'name' => 'testpaper',
            'description' => 'testpaper description',
            'mode' => 'range',
            'ranges' => array('courseId' => 0),
            'counts' => array('choice' => 2, 'fill' => 2),
            'scores' => array('choice' => 2, 'fill' => 2),
            'missScores' => array('choice' => 1, 'uncertain_choice' => 1),
            'courseSetId' => 1,
            'passedScore' => 60,
            'courseId' => 0,
            'pattern' => 'questionType',
            'type' => 'testpaper',
        );
        $testpaper = $this->getTestpaperService()->buildTestpaper($fields1, 'testpaper');
        $fields = array(
            'lessonId' => 1,
            'courseId' => 1,
        );
        $testpaperResult = $this->getTestpaperService()->startTestpaper($testpaper['id'], $fields);
        $this->assertEquals('doing', $testpaperResult['status']);

        $answers = array(
            $choiceQuestions[0]['id'] => array(1, 2),
            $fillQuestions[0]['id'] => array('fill answer'),
        );
        $formData = array(
            'usedTime' => 5,
            'data' => json_encode($answers),
            'attachments' => array(),
        );

        $result = $this->getTestpaperService()->finishTest($testpaperResult['id'], $formData);
        $itemResults = $this->getTestpaperService()->findItemResultsByResultId($result['id']);

        $scoreResult = $this->getTestpaperService()->sumScore($itemResults);
        $this->assertEquals(2, $scoreResult['sumScore']);
        $this->assertEquals(1, $scoreResult['rightItemCount']);
    }

    public function testFindAttachments()
    {
        $testpaper = $this->createTestpaper1();
        $attachments = $this->getTestpaperService()->findAttachments($testpaper['id']);

        $this->assertEmpty($attachments);
    }

    /**
     * @expectedException \Biz\User\UserException
     * @expectedExceptionMessage exception.user.unlogin
     */
    public function testCanLookTestpaperUnlogin()
    {
        $biz = $this->getBiz();
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 0,
            'nickname' => '游客',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'),
            'org' => array('id' => 1),
        ));
        $biz['user'] = $currentUser;

        $this->getTestpaperService()->canLookTestpaper(123);
    }

    /**
     * @expectedException \Biz\Testpaper\TestpaperException
     * @expectedExceptionMessage exception.testpaper.not_found_result
     */
    public function testCanLookTestpaperResultEmpty()
    {
        $this->getTestpaperService()->canLookTestpaper(123);
    }

    /**
     * @expectedException \Biz\Testpaper\TestpaperException
     * @expectedExceptionMessage exception.testpaper.not_found
     */
    public function testCanLookTestpaperEmpty()
    {
        $fields = array(
            'paperName' => 'testpaper name',
            'testId' => 1,
            'userId' => 1,
            'limitedTime' => 0,
            'beginTime' => time(),
            'status' => 'doing',
            'usedTime' => 0,
            'courseId' => 0,
            'courseSetId' => 2,
            'lessonId' => 0,
            'type' => 'testpaper',
        );
        $result = $this->getTestpaperService()->addTestpaperResult($fields);

        $this->getTestpaperService()->canLookTestpaper($result['id']);
    }

    /**
     * @expectedException \Biz\Testpaper\TestpaperException
     * @expectedExceptionMessage exception.testpaper.forbidden_access_testpaper
     */
    public function testCanLookTestpaperStatusError()
    {
        $testpaper = $this->createTestpaper1();

        $fields = array(
            'paperName' => 'testpaper name',
            'testId' => $testpaper['id'],
            'userId' => 123,
            'limitedTime' => 0,
            'beginTime' => time(),
            'status' => 'doing',
            'usedTime' => 0,
            'courseId' => 0,
            'courseSetId' => 2,
            'lessonId' => 0,
            'type' => 'testpaper',
        );
        $result = $this->getTestpaperService()->addTestpaperResult($fields);

        $this->getTestpaperService()->canLookTestpaper($result['id']);
    }

    public function testAdminLookTestpaper()
    {
        $testpaper = $this->createTestpaper1();

        $fields = array(
            'paperName' => 'testpaper name',
            'testId' => $testpaper['id'],
            'userId' => 123,
            'limitedTime' => 0,
            'beginTime' => time(),
            'status' => 'reviewing',
            'usedTime' => 0,
            'courseId' => 1,
            'courseSetId' => 2,
            'lessonId' => 0,
            'type' => 'testpaper',
        );
        $result = $this->getTestpaperService()->addTestpaperResult($fields);

        $canLook = $this->getTestpaperService()->canLookTestpaper($result['id']);
        $this->assertTrue($canLook);
    }

    public function testTeacherLookTestpaper()
    {
        $this->_setCurrentUser();
        $testpaper = $this->createTestpaper1();

        $fields = array(
            'paperName' => 'testpaper name',
            'testId' => $testpaper['id'],
            'userId' => 123,
            'limitedTime' => 0,
            'beginTime' => time(),
            'status' => 'reviewing',
            'usedTime' => 0,
            'courseId' => 1,
            'courseSetId' => 2,
            'lessonId' => 0,
            'type' => 'testpaper',
        );
        $result = $this->getTestpaperService()->addTestpaperResult($fields);

        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getCourse',
                'returnValue' => array('id' => 1),
            ),
        ));

        $this->mockBiz('Course:MemberService', array(
            array(
                'functionName' => 'getCourseMember',
                'returnValue' => array('role' => 'teacher'),
            ),
        ));

        $canLook = $this->getTestpaperService()->canLookTestpaper($result['id']);
        $this->assertTrue($canLook);
    }

    public function testLookTestpaperSameUser()
    {
        $this->_setCurrentUser();
        $testpaper = $this->createTestpaper1();

        $user = $this->getCurrentuser();
        $fields = array(
            'paperName' => 'testpaper name',
            'testId' => $testpaper['id'],
            'userId' => $user['id'],
            'limitedTime' => 0,
            'beginTime' => time(),
            'status' => 'reviewing',
            'usedTime' => 0,
            'courseId' => 1,
            'courseSetId' => 2,
            'lessonId' => 0,
            'type' => 'testpaper',
        );
        $result = $this->getTestpaperService()->addTestpaperResult($fields);

        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getCourse',
                'returnValue' => array('id' => 1),
            ),
        ));

        $this->mockBiz('Course:MemberService', array(
            array(
                'functionName' => 'getCourseMember',
                'returnValue' => array('role' => 'student'),
            ),
        ));

        $canLook = $this->getTestpaperService()->canLookTestpaper($result['id']);
        $this->assertTrue($canLook);
    }

    public function testLookTestpaperClassroomCourse()
    {
        $this->_setCurrentUser();
        $testpaper = $this->createTestpaper1();

        $fields = array(
            'paperName' => 'testpaper name',
            'testId' => $testpaper['id'],
            'userId' => 123,
            'limitedTime' => 0,
            'beginTime' => time(),
            'status' => 'reviewing',
            'usedTime' => 0,
            'courseId' => 2,
            'courseSetId' => 2,
            'lessonId' => 0,
            'type' => 'testpaper',
        );
        $result = $this->getTestpaperService()->addTestpaperResult($fields);

        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getCourse',
                'returnValue' => array('id' => 2, 'parentId' => 1),
            ),
        ));

        $this->mockBiz('Course:MemberService', array(
            array(
                'functionName' => 'getCourseMember',
                'returnValue' => array('role' => 'student'),
            ),
        ));

        $this->mockBiz('Classroom:ClassroomService', array(
            array(
                'functionName' => 'getClassroomByCourseId',
                'returnValue' => array('id' => 1),
            ),
            array(
                'functionName' => 'getClassroomMember',
                'returnValue' => array('role' => array('teacher')),
            ),
        ));

        $canLook = $this->getTestpaperService()->canLookTestpaper($result['id']);
        $this->assertTrue($canLook);
    }

    public function testNotLookTestpaper()
    {
        $this->_setCurrentUser();
        $testpaper = $this->createTestpaper1();

        $fields = array(
            'paperName' => 'testpaper name',
            'testId' => $testpaper['id'],
            'userId' => 123,
            'limitedTime' => 0,
            'beginTime' => time(),
            'status' => 'reviewing',
            'usedTime' => 0,
            'courseId' => 2,
            'courseSetId' => 2,
            'lessonId' => 0,
            'type' => 'testpaper',
        );
        $result = $this->getTestpaperService()->addTestpaperResult($fields);

        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getCourse',
                'returnValue' => array('id' => 2, 'parentId' => 0),
            ),
        ));

        $this->mockBiz('Course:MemberService', array(
            array(
                'functionName' => 'getCourseMember',
                'returnValue' => array('role' => 'student'),
            ),
        ));

        $canLook = $this->getTestpaperService()->canLookTestpaper($result['id']);
        $this->assertFalse($canLook);
    }

    public function testFindTestResultsByTestpaperIdAndUserIds()
    {
        $results = $this->getTestpaperService()->findTestResultsByTestpaperIdAndUserIds(array(1, 2, 3), 1);
        $this->assertEmpty($results);

        $fields = array(
            'paperName' => 'testpaper name',
            'testId' => 1,
            'userId' => 1,
            'limitedTime' => 0,
            'beginTime' => time(),
            'status' => 'finished',
            'usedTime' => 30,
            'courseId' => 1,
            'courseSetId' => 1,
            'lessonId' => 1,
            'type' => 'testpaper',
            'score' => 1,
        );
        $this->getTestpaperService()->addTestpaperResult($fields);

        $fields = array(
            'paperName' => 'testpaper name',
            'testId' => 1,
            'userId' => 2,
            'limitedTime' => 0,
            'beginTime' => time(),
            'status' => 'finished',
            'usedTime' => 20,
            'courseId' => 1,
            'courseSetId' => 1,
            'lessonId' => 1,
            'type' => 'testpaper',
            'score' => 2,
        );
        $this->getTestpaperService()->addTestpaperResult($fields);

        $fields = array(
            'paperName' => 'testpaper name',
            'testId' => 1,
            'userId' => 1,
            'limitedTime' => 0,
            'beginTime' => time(),
            'status' => 'finished',
            'usedTime' => 10,
            'courseId' => 1,
            'courseSetId' => 1,
            'lessonId' => 1,
            'type' => 'testpaper',
            'score' => 3,
        );
        $this->getTestpaperService()->addTestpaperResult($fields);

        $results = $this->getTestpaperService()->findTestResultsByTestpaperIdAndUserIds(array(1, 2), 1);

        $this->assertEquals(2, count($results));

        $this->assertEquals(0.5, $results[1]['usedTime']);
        $this->assertEquals(1, $results[1]['firstScore']);
        $this->assertEquals(3, $results[1]['maxScore']);

        $this->assertEquals(0.3, $results[2]['usedTime']);
        $this->assertEquals(2, $results[2]['firstScore']);
        $this->assertEquals(2, $results[2]['maxScore']);
    }

    public function testFindResultsByTestIdAndActivityId()
    {
        $fields = array(
            'paperName' => 'testpaper name',
            'testId' => 1,
            'userId' => 1,
            'limitedTime' => 0,
            'beginTime' => time(),
            'status' => 'finished',
            'usedTime' => 30,
            'courseId' => 1,
            'courseSetId' => 1,
            'lessonId' => 1,
            'type' => 'testpaper',
            'score' => 1,
            'passedStatus' => 'passed',
        );
        $this->getTestpaperService()->addTestpaperResult($fields);

        $fields = array(
            'paperName' => 'testpaper name',
            'testId' => 1,
            'userId' => 2,
            'limitedTime' => 0,
            'beginTime' => time(),
            'status' => 'finished',
            'usedTime' => 20,
            'courseId' => 1,
            'courseSetId' => 1,
            'lessonId' => 1,
            'type' => 'testpaper',
            'score' => 2,
            'passedStatus' => 'good',
        );
        $this->getTestpaperService()->addTestpaperResult($fields);

        $fields = array(
            'paperName' => 'testpaper name',
            'testId' => 1,
            'userId' => 1,
            'limitedTime' => 0,
            'beginTime' => time(),
            'status' => 'reviewing',
            'usedTime' => 10,
            'courseId' => 1,
            'courseSetId' => 1,
            'lessonId' => 1,
            'type' => 'testpaper',
            'score' => 3,
            'passedStatus' => 'none',
        );
        $this->getTestpaperService()->addTestpaperResult($fields);

        $results = $this->getTestpaperService()->findResultsByTestIdAndActivityId(1, 1);

        $this->assertEquals(2, count($results));

        $this->assertEquals(0.5, $results[1]['usedTime']);
        $this->assertEquals(1, $results[1]['firstScore']);
        $this->assertEquals(1, $results[1]['maxScore']);
        $this->assertEquals('passed', $results[1]['firstPassedStatus']);
        $this->assertEquals('passed', $results[1]['maxPassedStatus']);

        $this->assertEquals(0.3, $results[2]['usedTime']);
        $this->assertEquals(2, $results[2]['firstScore']);
        $this->assertEquals(2, $results[2]['maxScore']);
        $this->assertEquals('good', $results[2]['firstPassedStatus']);
        $this->assertEquals('good', $results[2]['maxPassedStatus']);
    }

    public function testGetNextReviewingResult()
    {
        $results = $this->getTestpaperService()->getNextReviewingResult(array(1, 2), 1, 'testpaper');
        $this->assertEmpty($results);

        $fields = array(
            'paperName' => 'testpaper name',
            'testId' => 1,
            'userId' => 2,
            'limitedTime' => 0,
            'beginTime' => time(),
            'status' => 'reviewing',
            'usedTime' => 20,
            'courseId' => 1,
            'courseSetId' => 1,
            'lessonId' => 1,
            'type' => 'testpaper',
            'score' => 2,
        );
        $this->getTestpaperService()->addTestpaperResult($fields);

        $results = $this->getTestpaperService()->getNextReviewingResult(array(1), 1, 'testpaper');
        $this->assertEmpty($results);

        $testpaper = $this->createTestpaper1();
        $fields = array(
            'paperName' => 'testpaper name',
            'testId' => $testpaper['id'],
            'userId' => 2,
            'limitedTime' => 0,
            'beginTime' => time(),
            'status' => 'reviewing',
            'usedTime' => 20,
            'courseId' => 1,
            'courseSetId' => 1,
            'lessonId' => 2,
            'type' => 'testpaper',
            'score' => 2,
        );
        $result = $this->getTestpaperService()->addTestpaperResult($fields);

        $next = $this->getTestpaperService()->getNextReviewingResult(array(1), 2, 'testpaper');
        $this->assertArrayEquals($result, $next);
    }

    public function testUpdateTestpaperItems()
    {
        $result = $this->getTestpaperService()->updateTestpaperItems(123, array('questions' => array()));
        $this->assertFalse($result);

        $choiceQuestions = $this->generateChoiceQuestions(1, 4);
        $fillQuestions = $this->generateFillQuestions(1, 2);
        $determineQuestions = $this->generateDetermineQuestions(1, 2);
        $essayQuestions = $this->generateEssayQuestions(1, 1);

        $fields1 = array(
            'name' => 'testpaper',
            'description' => 'testpaper description',
            'mode' => 'range',
            'ranges' => array('courseId' => 0),
            'counts' => array('choice' => 1, 'fill' => 1),
            'scores' => array('choice' => 2, 'fill' => 2),
            'missScores' => array('choice' => 1, 'uncertain_choice' => 1),
            'courseSetId' => 1,
            'courseId' => 0,
            'pattern' => 'questionType',
            'type' => 'testpaper',
        );
        $testpaper = $this->getTestpaperService()->buildTestpaper($fields1, 'testpaper');

        $items = array(
            'questions' => array(
                array('id' => $choiceQuestions[0]['id'], 'score' => 4, 'missScores' => 2, 'type' => 'choice'),
                array('id' => $choiceQuestions[1]['id'], 'score' => 4, 'missScores' => 2, 'type' => 'choice'),
                array('id' => $choiceQuestions[2]['id'], 'score' => 4, 'missScores' => 2, 'type' => 'choice'),
            ),
        );
        $this->getQuestionService()->delete($choiceQuestions[2]['id']);

        $testpaper = $this->getTestpaperService()->updateTestpaperItems($testpaper['id'], $items);
        $this->assertEquals(2, $testpaper['itemCount']);

        $result = $this->getTestpaperService()->updateTestpaperItems($testpaper['id'], array('questions' => array()));
        $this->assertFalse($result);
    }

    /**
     * @expectedException \Exception
     */
    public function testUpdateTestpaperItemsException()
    {
        $testpaper = $this->createTestpaper1();

        $this->mockBiz('Testpaper:TestpaperItemDao', array(
            array(
                'functionName' => 'deleteItemsByTestpaperId',
                'throwException' => new \Exception(),
            ),
        ));
        $result = $this->getTestpaperService()->updateTestpaperItems($testpaper['id'], array('questions' => array(array('id' => 1))));
    }

    /**
     * @expectedException \Biz\Testpaper\TestpaperException
     * @expectedExceptionMessage exception.testpaper.not_found
     */
    public function testUpdateTestpaperItemsTestpaperEmpty()
    {
        $items = array(
            'questions' => array(
                array('id' => 1),
                array('id' => 2),
                array('id' => 3),
            ),
        );

        $this->getTestpaperService()->updateTestpaperItems(123, $items);
    }

    /**
     * @expectedException \Biz\Testpaper\TestpaperException
     */
    public function testUpdateTestpaperItemsEmptyTestpaper()
    {
        $this->getTestpaperService()->updateTestpaperItems(123, array('questions' => array(array('id' => 1))));
    }

    public function testFindExamFirstResults()
    {
        $testpaper = $this->createTestpaper1();
        $testpaperResult1 = $this->createTestpaperResult1($testpaper);
        $testpaperResult2 = $this->createTestpaperResult2($testpaper);
        $testpaperResult3 = $this->createTestpaperResult3($testpaper);

        $results = $this->getTestpaperService()->findExamFirstResults($testpaper['id'], $testpaper['type'], 1);

        $this->assertArrayEquals($testpaperResult1, $results[1]);
        $this->assertArrayEquals($testpaperResult2, $results[2]);
    }

    public function testGetCheckedQuestionTypeBySeq()
    {
        $customFields = array(
            'metas' => array(
                'ranges' => array('courseId' => 0),
                'counts' => array(
                    'choice' => 1,
                    'fill' => 1,
                    'determine' => 1,
                ),
            ),
        );
        $testpaper = $this->createTestpaper1($customFields);
        $checkedQuestionTypes = $this->getTestpaperService()->getCheckedQuestionTypeBySeq($testpaper);
        $this->assertEquals(array('choice', 'fill', 'determine'), $checkedQuestionTypes);
    }

    public function testGetCheckedQuestionTypeBySeqEmpty()
    {
        $testpaper = $this->createTestpaper1();
        $checkedQuestionTypes = $this->getTestpaperService()->getCheckedQuestionTypeBySeq($testpaper);
        $this->assertEmpty($checkedQuestionTypes);
    }

    protected function createTestpaper1($customFields = array())
    {
        $fields = array(
            'name' => 'testpaper',
            'description' => 'testpaper description',
            'courseSetId' => 1,
            'courseId' => 0,
            'pattern' => 'questionType',
            'metas' => array(
                'ranges' => array('courseId' => 0),
            ),
            'type' => 'testpaper',
        );
        $fields = array_merge($fields, $customFields);

        return $this->getTestpaperService()->createTestpaper($fields);
    }

    public function testBuildExportTestpaperItems()
    {
        $customFields = array(
            'metas' => array(
                'ranges' => array('courseId' => 0),
                'counts' => array(
                    'choice' => 1,
                    'fill' => 1,
                    'determine' => 1,
                    'material' => 1,
                ),
            ),
        );
        $testpaper = $this->createTestpaper1($customFields);
        $testpaperItems = $this->createTestpaperItemWithMaterial($testpaper);
        $result = $this->getTestpaperService()->buildExportTestpaperItems($testpaper['id']);
        $this->assertEquals($testpaperItems[0]['questionType'], $result[0]['type']);
    }

    public function testImportTestpaper()
    {
        $importData = array(
            'title' => 'test title',
            'questions' => array(
                array(
                    'stem' => 'question stem',
                    'type' => 'choice',
                    'options' => array('option1', 'option2', 'option3', 'option4'),
                    'score' => '2.0',
                    'missScore' => '1.0',
                    'difficulty' => 'normal',
                    'answers' => array(0, 1),
                ),
                array(
                    'stem' => 'question stem',
                    'type' => 'material',
                    'score' => '2.0',
                    'difficulty' => 'normal',
                    'subQuestions' => array(
                        array(
                            'stem' => 'question stem',
                            'type' => 'choice',
                            'options' => array('option1', 'option2', 'option3', 'option4'),
                            'score' => '2.0',
                            'missScore' => '1.0',
                            'difficulty' => 'normal',
                            'answers' => array(0, 1),
                        ),
                    ),
                ),
            ),
        );

        $token = array(
            'token' => 'testtoken',
            'data' => array(
                'courseSetId' => 1,
            ),
        );

        $testpaper = $this->getTestpaperService()->importTestpaper($importData, $token);
        $this->assertEquals($importData['title'], $testpaper['name']);
        $this->assertEquals($token['data']['courseSetId'], $testpaper['courseSetId']);
    }

    protected function createHomework()
    {
        $fields = array(
            'name' => 'homework',
            'description' => 'homework description',
            'courseSetId' => 1,
            'courseId' => 1,
            'pattern' => 'questionType',
            'metas' => array(),
            'type' => 'homework',
            'status' => 'open',
        );

        return $this->getTestpaperService()->createTestpaper($fields);
    }

    protected function createExercise()
    {
        $fields = array(
            'name' => 'exercise',
            'description' => 'exercise description',
            'itemCount' => 1,
            'courseSetId' => 1,
            'courseId' => 1,
            'pattern' => 'questionType',
            'metas' => array(
                'questionTypes' => array('choice', 'single_choice', 'fill', 'determine'),
                'difficulty' => 'normal',
                'range' => array('courseId' => 0),
            ),
            'type' => 'exercise',
            'status' => 'open',
            'passedCondition' => array(0),
        );

        return $this->getTestpaperService()->createTestpaper($fields);
    }

    protected function createTestpaperItem($testpaper)
    {
        $choiceQuestions = $this->generateChoiceQuestions($testpaper['courseSetId'], 1);
        $fillQuestions = $this->generateFillQuestions($testpaper['courseSetId'], 1);
        $determineQuestions = $this->generateDetermineQuestions($testpaper['courseSetId'], 1);

        $questions = array_merge($choiceQuestions, $fillQuestions, $determineQuestions);

        $items = array();
        $seq = 1;
        foreach ($questions as $question) {
            $fields = array(
                'testId' => $testpaper['id'],
                'seq' => $seq,
                'questionId' => $question['id'],
                'questionType' => $question['type'],
                'parentId' => $question['parentId'],
                'score' => $question['score'],
                'missScore' => 0,
                'type' => $testpaper['type'],
            );
            $items[] = $this->getTestpaperService()->createItem($fields);
            ++$seq;
        }

        return $items;
    }

    protected function createTestpaperItemWithMaterial($testpaper)
    {
        $choiceQuestions = $this->generateChoiceQuestions($testpaper['courseSetId'], 1);
        $fillQuestions = $this->generateFillQuestions($testpaper['courseSetId'], 1);
        $determineQuestions = $this->generateDetermineQuestions($testpaper['courseSetId'], 1);
        $materialQuestions = $this->generateMaterialQuestions($testpaper['courseSetId'], 1);
        $subChoiceQuestions = $this->generateChoiceQuestions($testpaper['courseSetId'], 1, null, $materialQuestions[0]['id']);
        $questions = array_merge($choiceQuestions, $fillQuestions, $determineQuestions, $materialQuestions, $subChoiceQuestions);

        $items = array();
        $seq = 1;
        foreach ($questions as $question) {
            $fields = array(
                'testId' => $testpaper['id'],
                'seq' => $seq,
                'questionId' => $question['id'],
                'questionType' => $question['type'],
                'parentId' => $question['parentId'],
                'score' => $question['score'],
                'missScore' => 0,
                'type' => $testpaper['type'],
            );
            $items[] = $this->getTestpaperService()->createItem($fields);
            ++$seq;
        }

        return $items;
    }

    protected function createSingleItem()
    {
        $fields = array(
            'testId' => 1,
            'seq' => 1,
            'questionId' => 1,
            'questionType' => 'choice',
            'parentId' => 0,
            'score' => '2',
            'missScore' => 0,
        );

        return $this->getTestpaperService()->createItem($fields);
    }

    protected function createTestpaperResult1($testpaper)
    {
        $fields = array(
            'paperName' => $testpaper['name'],
            'testId' => $testpaper['id'],
            'userId' => 1,
            'limitedTime' => $testpaper['limitedTime'],
            'beginTime' => time(),
            'status' => 'doing',
            'usedTime' => 0,
            'courseId' => 1,
            'courseSetId' => $testpaper['courseSetId'],
            'lessonId' => 1,
            'type' => $testpaper['type'],
        );

        return $this->getTestpaperService()->addTestpaperResult($fields);
    }

    protected function createTestpaperResult2($testpaper)
    {
        $fields = array(
            'paperName' => $testpaper['name'],
            'testId' => $testpaper['id'],
            'userId' => 2,
            'limitedTime' => $testpaper['limitedTime'],
            'beginTime' => time(),
            'status' => 'doing',
            'usedTime' => 0,
            'courseId' => 1,
            'courseSetId' => $testpaper['courseSetId'],
            'lessonId' => 1,
            'type' => $testpaper['type'],
        );

        return $this->getTestpaperService()->addTestpaperResult($fields);
    }

    protected function createTestpaperResult3($testpaper)
    {
        $fields = array(
            'paperName' => $testpaper['name'],
            'testId' => $testpaper['id'],
            'userId' => 1,
            'limitedTime' => $testpaper['limitedTime'],
            'score' => '5',
            'endTime' => time(),
            'beginTime' => time(),
            'status' => 'finished',
            'usedTime' => 0,
            'courseId' => 1,
            'courseSetId' => $testpaper['courseSetId'],
            'lessonId' => 1,
            'type' => $testpaper['type'],
        );

        return $this->getTestpaperService()->addTestpaperResult($fields);
    }

    protected function generateChoiceQuestions($courseId, $count, $difficulty = null, $parentId = 0)
    {
        $questions = array();
        for ($i = 0; $i < $count; ++$i) {
            $question = array(
                'type' => 'choice',
                'stem' => 'test single choice question.',
                'choices' => array(
                    'question -> choice 1',
                    'question -> choice 2',
                    'question -> choice 3',
                    'question -> choice 4',
                ),
                'answer' => array(1, 2),
                'courseSetId' => $courseId,
                'target' => 'course/'.$courseId,
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
                'parentId' => $parentId,
            );

            $questions[] = $this->getQuestionService()->create($question);
        }

        return $questions;
    }

    protected function generateFillQuestions($courseId, $count, $difficulty = null, $parentId = 0)
    {
        $questions = array();
        for ($i = 0; $i < $count; ++$i) {
            $question = array(
                'type' => 'fill',
                'stem' => 'fill question [[aaa]].',
                'target' => 'course/'.$courseId,
                'courseSetId' => $courseId,
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
                'parentId' => $parentId,
            );

            $questions[] = $this->getQuestionService()->create($question);
        }

        return $questions;
    }

    protected function generateDetermineQuestions($courseId, $count, $difficulty = null, $parentId = 0)
    {
        $questions = array();
        for ($i = 0; $i < $count; ++$i) {
            $question = array(
                'type' => 'determine',
                'stem' => 'determine question.',
                'target' => 'course/'.$courseId,
                'courseSetId' => $courseId,
                'answer' => array(0),
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
                'parentId' => $parentId,
            );

            $questions[] = $this->getQuestionService()->create($question);
        }

        return $questions;
    }

    protected function generateEssayQuestions($courseId, $count, $difficulty = null, $parentId = 0)
    {
        $questions = array();
        for ($i = 0; $i < $count; ++$i) {
            $question = array(
                'type' => 'essay',
                'stem' => 'essay question.',
                'target' => 'course/'.$courseId,
                'courseSetId' => $courseId,
                'answer' => array('xxx'),
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
                'parentId' => $parentId,
            );

            $questions[] = $this->getQuestionService()->create($question);
        }

        return $questions;
    }

    protected function generateMaterialQuestions($courseId, $count, $difficulty = null)
    {
        $questions = array();
        for ($i = 0; $i < $count; ++$i) {
            $question = array(
                'type' => 'material',
                'stem' => 'material question.',
                'target' => 'course/'.$courseId,
                'courseSetId' => $courseId,
                'difficulty' => empty($difficulty) ? 'normal' : $difficulty,
            );

            $questions[] = $this->getQuestionService()->create($question);
        }

        return $questions;
    }

    private function _setCurrentUser()
    {
        $biz = $this->getBiz();

        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'user1',
            'email' => 'user1@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));
        $biz['user'] = $currentUser;
    }

    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getTestpaperResultDao()
    {
        return $this->createDao('Testpaper:TestpaperResultDao');
    }
}
