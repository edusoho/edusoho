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
		return QuestionSerialize::unserializes($this->getQuestionDao()->findQuestionsByIds($ids));
	}

	public function findFavoriteQuestionsByUserId ($id, $start, $limit)
	{
		return $this->getQuestionFavoriteDao()->findFavoriteQuestionsByUserId($id, $start, $limit);
	}

	public function findFavoriteQuestionsCountByUserId ($id)
	{
		return $this->getQuestionFavoriteDao()->findFavoriteQuestionsCountByUserId($id);
	}

	public function findAllFavoriteQuestionsByUserId ($id)
	{
		return $this->getQuestionFavoriteDao()->findAllFavoriteQuestionsByUserId($id);
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

	public function findTestPaperResultsByStatusAndTeacherIds ($ids, $status, $start, $limit)
	{
		return $this->getTestPaperResultDao()->findTestPaperResultsByStatusAndTeacherIds($ids, $status, $start, $limit);
	}

	public function findTestPaperResultCountByStatusAndTeacherIds ($ids, $status)
	{
		return $this->getTestPaperResultDao()->findTestPaperResultCountByStatusAndTeacherIds($ids, $status);
	}

	public function findFavoriteQuestionsByIds ($questionIds)
	{
		$questions = QuestionSerialize::unserializes($this->getQuestionDao()->findQuestionsByIds($questionIds));

		$questionParents = QuestionSerialize::unserializes($this->getQuestionDao()->findQuestionsByIds(ArrayToolkit::column($questions, 'parentId')));

		$questions = array_merge($questions, $questionParents);

		foreach ($questions as $key => $value) {
			if ($value['type'] == 'fill'){
				foreach ($value['answer'] as $k => $v) {
					$questions[$key]['answer'][$k] = str_replace('|', '或者', $v);
				}
			}
		}

		

        return $questions;
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

	private function getQuestionChoiceDao(){
	    return $this->createDao('Quiz.QuizQuestionChoiceDao');
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

class TestQuestion
{
    public static function makeMaterial ($questions)
    {
        foreach ($questions as $key => $value) {
            if ($value['targetId'] == 0) {
                if (!array_key_exists('questions', $questions[$value['parentId']])) {
                    $questions[$value['parentId']]['questions'] = array();
                }
                $questions[$value['parentId']]['questions'][$value['id']] = $value;
                unset($questions[$value['id']]);
            }
        }

        return $questions;
    }
}