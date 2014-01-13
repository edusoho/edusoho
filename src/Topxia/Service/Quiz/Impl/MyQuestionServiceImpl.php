<?php
namespace Topxia\Service\Quiz\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;

class MyQuestionServiceImpl extends BaseService
{
	public function getTestPaper($id)
    {
        return TestPaperSerialize::unserialize($this->getTestPaperDao()->getTestPaper($id));
    }

    public function getTestPaperResult($id)
    {
        return $this->getTestPaperResultDao()->getResult($id);
    }

	public function findTestPaperResultsByUserId ($id, $start, $limit)
	{
		return $this->getTestPaperResultDao()->findTestPaperResultsByUserId($id, $start, $limit);
	}

	public function findTestPaperResultsCountByUserId ($id)
	{
		return $this->getTestPaperResultDao()->findTestPaperResultsCountByUserId($id);
	}

	public function findTestPapersByIds($ids)
	{
		return $this->getTestPaperDao()->findTestPaperByIds($ids);
	}

	public function findTestPaperResultsByIds($ids)
	{
		return $this->getTestPaperResultDao()->findResultByIds($ids);
	}


	public function findWrongResultByUserId ($id, $start, $limit)
	{
		return $this->getDoTestDao()->findWrongResultByUserId($id, $start, $limit);
	}

	public function findWrongResultCountByUserId ($id)
	{
		return $this->getDoTestDao()->findWrongResultCountByUserId($id);
	}

	public function findQuestionsByIds ($ids)
	{
		return $this->getQuestionDao()->findQuestionsByIds($ids);
	}

	public function favoriteQuestion($questionId, $testPaperResultId, $userId)
	{
		$favorite = array(
			'questionId' => $questionId,
			'testPaperResultId' => $testPaperResultId,
			'userId' => $userId,
			'createdTime' => time()
		);

		$favoriteBack = $this->getQuestionFavoriteDao()->getFavoriteByQuestionIdAndTestPaperResutlIdAndUserId($favorite);

		if (!$favoriteBack) {
			return $this->getQuestionFavoriteDao()->addFavorite($favorite);
		}

		return $favoriteBack;
	}

	public function unFavoriteQuestion ($questionId, $testPaperResultId, $userId)
	{
		$favorite = array(
			'questionId' => $questionId,
			'testPaperResultId' => $testPaperResultId,
			'userId' => $userId
		);

		return $this->getQuestionFavoriteDao()->deleteFavorite($favorite);
	}

	public function findFavoriteQuestionsByUserId ($id, $start, $limit)
	{
		return $this->getQuestionFavoriteDao()->findFavoriteQuestionsByUserId($id, $start, $limit);
	}

	public function findFavoriteQuestionsCountByUserId ($id)
	{
		return $this->getQuestionFavoriteDao()->findFavoriteQuestionsCountByUserId($id);
	}

	public function findTeacherTestPapersByTeacherId ($teacherId)
	{
		$members = $this->getMemberDao()->findAllMemberByUserIdAndRole($teacherId, 'teacher');

		return $this->getTestPaperDao()->findTestPaperByTargetIdsAndTargetType(ArrayToolkit::column($members, 'courseId'), 'course');
	}

	public function findTestPaperResultsByStatusAndTestIds ($ids, $status, $start, $limit)
	{
		return $this->getTestPaperResultDao()->findTestPaperResultsByStatusAndTestIds($ids, $status, $start, $limit);
	}

	public function findTestPaperResultCountByStatusAndTestIds ($ids, $status)
	{
		return $this->getTestPaperResultDao()->findTestPaperResultCountByStatusAndTestIds($ids, $status);
	}

	public function findUsersByIds ($ids)
	{
		return $this->getUserDao()->findUsersByIds($ids);
	}

	private function getTestPaperResultDao()
    {
        return $this->createDao('Quiz.TestPaperResultDao');
    }

    private function getTestPaperDao()
    {
        return $this->createDao('Quiz.TestPaperDao');
    }

    private function getQuestionDao(){
	    return $this->createDao('Quiz.QuizQuestionDao');
	}

	private function getDoTestDao()
    {
        return $this->createDao('Quiz.DoTestDao');
    }

    private function getQuestionFavoriteDao()
    {
        return $this->createDao('Quiz.QuestionFavoriteDao');
    }

    private function getUserDao()
    {
        return $this->createDao('User.UserDao');
    }

    private function getMemberDao ()
    {
        return $this->createDao('Course.CourseMemberDao');
    }

    protected function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }
}