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

    public function favoriteQuestionAction(Request $request ,$id)
    {
        $targetType = $request->query->get('targetType');
        $targetId = $request->query->get('targetId');

        $user = $this->getCurrentUser();

        $favorite = $this->getQuestionService()->favoriteQuestion($id, $targetType, $targetId, $user['id']);
    
        return $this->createJsonResponse(true);
    }

    public function unFavoriteQuestionAction(Request $request ,$id)
    {
        $targetType = $request->query->get('targetType');
        $targetId = $request->query->get('targetId');

        $user = $this->getCurrentUser();

        $this->getQuestionService()->unFavoriteQuestion($id, $targetType, $targetId, $user['id']);

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

        $questions = $this->getMyQuestionService()->findFavoriteQuestionsByIds($questionIds);

        // $questions = $this->formatQuestions($questions);

        $myTestPaperIds = array();
        foreach ($favoriteQuestions as $key => $value) {
            if ($value['targetType'] == 'testpaper'){
                array_push($myTestPaperIds, $value['targetId']);
            }
        }

        $myTestPapers = $this->getMyQuestionService()->findTestPapersByIds($myTestPaperIds);
 
        return $this->render('TopxiaWebBundle:MyQuiz:my-favorite-question.html.twig', array(
            'favoriteActive' => 'active',
            'user' => $user,
            'favoriteQuestions' => ArrayToolkit::index($favoriteQuestions, 'id'),
            'testPapers' => ArrayToolkit::index($myTestPapers, 'id'),
            'questions' => ArrayToolkit::index($questions, 'id'),
            'paginator' => $paginator
        ));
    }

    public function listReviewingTestAction (Request $request)
    {
        $user = $this->getCurrentUser();

        $teacherTests = $this->getMyQuestionService()->findTeacherTestPapersByTeacherId($user['id']);

        $testPaperIds = ArrayToolkit::column($teacherTests, 'id');

        $testPapers = $this->getMyQuestionService()->findTestPapersByIds($testPaperIds);

        $paginator = new Paginator(
            $request,
            $this->getMyQuestionService()->findTestPaperResultCountByStatusAndTestIds($testPaperIds, 'reviewing'),
            10
        );

        $paperResults = $this->getMyQuestionService()->findTestPaperResultsByStatusAndTestIds(
            $testPaperIds,
            'reviewing',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        
        $testPaperIds = ArrayToolkit::column($paperResults, 'testId');

        $testPapers = $this->getMyQuestionService()->findTestPapersByIds($testPaperIds);

        $userIds = ArrayToolkit::column($paperResults, 'userId');

        $users = $this->getMyQuestionService()->findUsersByIds($userIds);

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($testPapers, 'targetId'));

        return $this->render('TopxiaWebBundle:MyQuiz:teacher-test-layout.html.twig', array(
            'status' => 'reviewing',
            'users' => ArrayToolkit::index($users, 'id'),
            'paperResults' => $paperResults,
            'courses' => ArrayToolkit::index($courses, 'id'),
            'testPapers' => ArrayToolkit::index($testPapers, 'id'),
            'teacher' => $user,
            'paginator' => $paginator
        ));
    }

    public function listFinishedTestAction (Request $request)
    {
        $user = $this->getCurrentUser();


        $paginator = new Paginator(
            $request,
            $this->getMyQuestionService()->findTestPaperResultCountByStatusAndTeacherIds(array($user['id']), 'finished'),
            10
        );

        $paperResults = $this->getMyQuestionService()->findTestPaperResultsByStatusAndTeacherIds(
            array($user['id']),
            'finished',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        
        $testPaperIds = ArrayToolkit::column($paperResults, 'testId');

        $testPapers = $this->getMyQuestionService()->findTestPapersByIds($testPaperIds);

        $userIds = ArrayToolkit::column($paperResults, 'userId');

        $users = $this->getMyQuestionService()->findUsersByIds($userIds);

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($testPapers, 'targetId'));

        return $this->render('TopxiaWebBundle:MyQuiz:teacher-test-layout.html.twig', array(
            'status' => 'finished',
            'users' => ArrayToolkit::index($users, 'id'),
            'paperResults' => $paperResults,
            'courses' => ArrayToolkit::index($courses, 'id'),
            'testPapers' => ArrayToolkit::index($testPapers, 'id'),
            'teacher' => $user,
            'paginator' => $paginator
        ));
    }

    private function formatQuestions ($questions)
    {
        $formatQuestions = array();

        foreach ($questions as $key => $value) {

            if(in_array($value['type'], array('single_choice', 'choice'))) {
                $i = 65;
                foreach ($value['choices'] as $key => $v) {
                    $v['choiceIndex'] = chr($i);
                    $value['choices'][$key] = $v;
                    $i++;
                }
            }

            
            $formatQuestions[$value['id']] = $value;
        }

        return $formatQuestions;
    }


	private function getMyQuestionService ()
	{
		return $this->getServiceKernel()->createService('Quiz.MyQuestionService');
	}

    private function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Quiz.QuestionService');
    }

	private function getCourseService ()
	{
		return $this->getServiceKernel()->createService('Course.CourseService');
	}
}