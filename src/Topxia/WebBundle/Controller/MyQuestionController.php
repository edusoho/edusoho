<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

use Topxia\Service\Quiz\Impl\QuestionSerialize;

class MyQuestionController extends BaseController
{
	public function indexAction (Request $request)
	{
		$user = $this->getCurrentUser();

        $paginator = new Paginator(
            $request,
            $this->getMyQuestionService()->findTestPaperResultsCountByUserId($user['id']),
            10
        );

        $myTestPaperResults = $this->getMyQuestionService()->findTestPaperResultsByUserId(
            $user['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $myTestPapersIds = ArrayToolkit::column($myTestPaperResults, 'testId');

        $myTestPapers = $this->getMyQuestionService()->findTestPapersByIds($myTestPapersIds);
        $myTestPapers = ArrayToolkit::index($myTestPapers, 'id');

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($myTestPapers, 'targetId'));

        return $this->render('TopxiaWebBundle:MyQuiz:my-quiz.html.twig', array(
            'myQuizActive' => 'active',
            'user' => $user,
            'myTestPaperResults' => $myTestPaperResults,
            'myTestPapers' => $myTestPapers,
            'courses' => $courses,
            'paginator' => $paginator
        ));
	}

    public function myWrongQuestionsAction (Request $request)
    {
        $user = $this->getCurrentUser();

        $paginator = new Paginator(
            $request,
            $this->getMyQuestionService()->findWrongResultCountByUserId($user['id']),
            10
        );

        $myWrongs = $this->getMyQuestionService()->findWrongResultByUserId(
            $user['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $questionIds = ArrayToolkit::column($myWrongs, 'questionId');
        $testPaperIds = ArrayToolkit::column($myWrongs, 'testId');
        $testPaperResultIds = ArrayToolkit::column($myWrongs, 'testPaperResultId');

        $testPapers = $this->getMyQuestionService()->findTestPapersByIds($testPaperIds);

        $testPaperResults = $this->getMyQuestionService()->findTestPaperResultsByIds($testPaperResultIds);

        $questions = $this->getMyQuestionService()->findQuestionsByIds($questionIds);

        return $this->render('TopxiaWebBundle:MyQuiz:my-wrong-question.html.twig', array(
            'myWrongQuestionActive' => 'active',
            'user' => $user,
            'myWrongs' => $myWrongs,
            'questions' => ArrayToolkit::index($questions, 'id'),
            'testPapers' => ArrayToolkit::index($testPapers, 'id'),
            'testPaperResults' => ArrayToolkit::index($testPaperResults, 'id'),
            'paginator' => $paginator
        ));
    }

    public function favoriteQuestionAction(Request $request ,$questionId, $testPaperResultId)
    {

        $user = $this->getCurrentUser();

        $favorite = $this->getMyQuestionService()->favoriteQuestion($questionId, $testPaperResultId, $user['id']);
    
        return $this->createJsonResponse(true);
    }

    public function unFavoriteQuestionAction(Request $request ,$questionId, $testPaperResultId)
    {
        $user = $this->getCurrentUser();

        $this->getMyQuestionService()->unFavoriteQuestion($questionId, $testPaperResultId, $user['id']);

        return $this->createJsonResponse(true);
    }

    public function showFavoriteQuestionAction (Request $request)
    {
        $user = $this->getCurrentUser();

        $paginator = new Paginator(
            $request,
            $this->getMyQuestionService()->findFavoriteQuestionsCountByUserId($user['id']),
            10
        );

        $favoriteQuestions = $this->getMyQuestionService()->findFavoriteQuestionsByUserId(
            $user['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $questionIds = ArrayToolkit::column($favoriteQuestions, 'questionId');

        $questions = $this->getMyQuestionService()->findQuestionsByIds($questionIds);

        $myTestPaperResultIds = ArrayToolkit::column($favoriteQuestions, 'testPaperResultId');

        $myTestPaperResults = $this->getMyQuestionService()->findTestPaperResultsByIds($myTestPaperResultIds);

        $myTestPaperIds = ArrayToolkit::column($myTestPaperResults, 'testId');

        $myTestPapers = $this->getMyQuestionService()->findTestPapersByIds($myTestPaperIds);
        
        return $this->render('TopxiaWebBundle:MyQuiz:my-favorite-question.html.twig', array(
            'favoriteActive' => 'active',
            'user' => $user,
            'favoriteQuestions' => $favoriteQuestions,
            'testPaperResults' => ArrayToolkit::index($myTestPaperResults, 'id'),
            'testPapers' => ArrayToolkit::index($myTestPapers, 'id'),
            'questions' => ArrayToolkit::index($questions, 'id'),
            'paginator' => $paginator
        ));
    }

    public function listReviewingTestAction (Request $request, $status)
    {
        $user = $this->getCurrentUser();

        $teacherTests = $this->getMyQuestionService()->findTeacherTestPapersByTeacherId($user['id']);

        $testPaperIds = ArrayToolkit::column($teacherTests, 'id');

        $testPapers = $this->getMyQuestionService()->findTestPapersByIds($testPaperIds);

        $paginator = new Paginator(
            $request,
            $this->getMyQuestionService()->findTestPaperResultCountByStatusAndTestIds($testPaperIds, $status),
            10
        );

        $reviewingTests = $this->getMyQuestionService()->findTestPaperResultsByStatusAndTestIds(
            $testPaperIds,
            $status,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        
        $testPaperIds = ArrayToolkit::column($reviewingTests, 'testId');

        $testPapers = $this->getMyQuestionService()->findTestPapersByIds($testPaperIds);

        $userIds = ArrayToolkit::column($reviewingTests, 'userId');

        $users = $this->getMyQuestionService()->findUsersByIds($userIds);

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($testPapers, 'targetId'));

        return $this->render('TopxiaWebBundle:MyQuiz:list-teacher-test.html.twig', array(
            'status' => $status,
            'users' => ArrayToolkit::index($users, 'id'),
            'reviewingTests' => $reviewingTests,
            'courses' => ArrayToolkit::index($courses, 'id'),
            'testPapers' => ArrayToolkit::index($testPapers, 'id'),
            'paginator' => $paginator
        ));
    }


	private function getMyQuestionService ()
	{
		return $this->getServiceKernel()->createService('Quiz.MyQuestionService');
	}

	private function getCourseService ()
	{
		return $this->getServiceKernel()->createService('Course.CourseService');
	}
}