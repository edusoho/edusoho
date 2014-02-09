<?php
namespace Topxia\Service\Testpaper\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Testpaper\TestpaperService;
use Topxia\Common\ArrayToolkit;

class TestpaperServiceImpl extends BaseService implements TestpaperService
{
	public function getTestpaper($id)
	{
		return $this->getTestpaperDao()->getTestpaper($id);
	}

    public function getTestpaperResult($id)
    {
        return $this->getTestpaperResultDao()->getTestpaperResult($id);
    }

	public function searchTestpapers($conditions, $sort, $start, $limit)
	{

	}

	public function searchTestpapersCount($conditions)
	{

	}

    public function publishTestpaper($id)
    {

    }

    public function closeTestpaper($id)
    {

    }

    public function deleteTestpaper($id)
    {

    }

    public function deleteTestpaperByIds(array $ids)
    {

    }

    public function buildTestpaper($id, $builder, $builderOptions)
    {

    }

    public function rebuildTestpaper($id, $builder, $builderOptions)
    {

    }




    public function findTestpaperResultsByTestpaperIdAndUserId($testpaperId, $userId)
    {
    	return $this->getTestpaperResultDao()->findTestpaperResultsByTestpaperIdAndUserId($testpaperId, $userId);
    }

    public function findTestPaperResultsByTestIdAndStatusAndUserId($testpaperId, $userId, array $status)
    {
    	return $this->getTestpaperResultDao()->findTestPaperResultsByTestIdAndStatusAndUserId($testpaperId, $status, $userId);
    }








    public function startTestpaper($id, $target)
    {
    	$testPaper = $this->getTestpaperDao()->getTestpaper($id);

    	$testPaperResult = array(
            'paperName' => $testPaper['name'],
            'testId' => $id,
            'userId' => $this->getCurrentUser()->id,
            'limitedTime' => $testPaper['limitedTime'],
            'beginTime' => time(),
            'status' => 'doing',
            'usedTime' => 0,
            'targetType' => empty($target['type']) ? '' : $target['type'],
            'targetId' => empty($target['id']) ? 0 : intval($target['id']),
        );

        return $this->getTestpaperResultDao()->addTestpaperResult($testPaperResult);
    }

    public function previewTestpaper($testpaperId)
    {
        $items = $this->getTestpaperItems($testpaperId);
        $items = ArrayToolkit::index($items, 'questionId');

        $questions = $this->getQuestionService()->findQuestionsByIds(ArrayToolkit::column($items, 'questionId'));
        $questions = ArrayToolkit::index($questions, 'id');

        $formatItems = array();
        foreach ($items as $questionId => $item) {
            $items[$questionId]['question'] = $questions[$questionId];

            if ($item['parentId'] != 0) {
                if (!array_key_exists('items', $items[$item['parentId']])) {
                    $items[$item['parentId']]['items'] = array();
                }
                $items[$item['parentId']]['items'][$questionId] = $items[$questionId];
                $formatItems['material'][$item['parentId']]['items'][$item['seq']] = $items[$questionId];
                unset($items[$questionId]);
            } else {
                $formatItems[$item['questionType']][$item['questionId']] = $items[$questionId];
            }

        }
        ksort($formatItems);
        return $formatItems;
    }

    public function showTestpaper($testpaperResultId)
    {
        $itemResults = $this->getTestpaperItemResultDao()->findTestResultsByTestPaperResultId($testpaperResultId);
        $itemResults = ArrayToolkit::index($itemResults, 'questionId');

        $testpaperResult = $this->getTestpaperResultDao()->getTestpaperResult($testpaperResultId);

        $items = $this->getTestpaperItems($testpaperResult['testId']);
        $items = ArrayToolkit::index($items, 'questionId');

        $questions = $this->getQuestionService()->findQuestionsByIds(ArrayToolkit::column($items, 'questionId'));
        $questions = ArrayToolkit::index($questions, 'id');

        $formatItems = array();
        foreach ($items as $questionId => $item) {

            if (array_key_exists($questionId, $itemResults)){
                $questions[$questionId]['testResult'] = $itemResults[$questionId];
            }

            $items[$questionId]['question'] = $questions[$questionId];

            if ($item['parentId'] != 0) {
                if (!array_key_exists('items', $items[$item['parentId']])) {
                    $items[$item['parentId']]['items'] = array();
                }
                $items[$item['parentId']]['items'][$questionId] = $items[$questionId];
                $formatItems['material'][$item['parentId']]['items'][$item['seq']] = $items[$questionId];
                unset($items[$questionId]);
            } else {
                $formatItems[$item['questionType']][$item['questionId']] = $items[$questionId];
            }

        }
        ksort($formatItems);
        return $formatItems;
    }


    public function finishTestpaper($resultId)
    {
        
    }

    public function submitTestpaperAnswer($resultId, $answers)
    {

    }

    public function reviewTestpaper($resultId, $items, $remark = null)
    {

    }

    public function getTestpaperItems($testpaperId)
    {
        return $this->getTestItemDao()->findItemsByTestPaperId($testpaperId);
    }

    public function addItem($testpaperId, $questionId, $afterItemId = null)
    {

    }

    public function replaceItem($testpaperId, $itemId, $questionId)
    {

    }


    public function canTeacherCheck($id)
    {
        $paper = $this->getTestPaperDao()->getTestPaper($id);
        if (!$paper) {
            throw $this->createServiceException('试卷不存在');
        }

        $user = $this->getCurrentUser();
        if ($user->isAdmin()) {
            return $user['id'];
        }

        $target = explode('-', $paper['target']);

        if ($target[0] == 'course') {
            $targetId = explode('/', $targetType[1]);
            $course = $this->getCourseService()->getCourse($targetId[0]);

            // @todo: 这个是有问题的。
            if (in_array($user['id'], $course['teacherIds'])) {
                return $user['id'];
            }
        }
        return false;
    }





	private function getTestpaperDao()
    {
        return $this->createDao('Testpaper.TestpaperDao');
    }

    private function getTestpaperResultDao()
    {
        return $this->createDao('Testpaper.TestpaperResultDao');
    }

    private function getTestItemDao(){
        return $this->createDao('Quiz.TestItemDao');
    }

    private function getTestpaperItemResultDao(){
        return $this->createDao('Testpaper.TestpaperItemResultDao');
    }

    private function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    private function getQuestionService()
    {
        return $this->createService('Question.QuestionService');
    }
}