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

        $questions = $this->getQuestionService()->findQuestionsByIds(ArrayToolkit::column($items, 'questionId'));
var_dump($questions);exit();
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

    private function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    private function getQuestionService()
    {
        return $this->createService('Question.QuestionService');
    }
}