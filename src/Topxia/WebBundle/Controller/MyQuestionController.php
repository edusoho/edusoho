<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

use Topxia\Service\Quiz\Impl\QuestionSerialize;


class MyQuestionController extends BaseController
{
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
            $this->getQuestionService()->findFavoriteQuestionsCountByUserId($user['id']),
            10
        );

        $favoriteQuestions = $this->getQuestionService()->findFavoriteQuestionsByUserId(
            $user['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
 
        $questionIds = ArrayToolkit::column($favoriteQuestions, 'questionId');

        $questions = $this->getQuestionService()->findQuestionsByIds($questionIds);

        $myTestpaperIds = array();
        foreach ($favoriteQuestions as $key => $value) {
            if ($value['targetType'] == 'testpaper'){
                array_push($myTestpaperIds, $value['targetId']);
            }
        }

        $myTestpapers = $this->getTestpaperService()->findTestpapersByIds($myTestpaperIds);
 
        return $this->render('TopxiaWebBundle:MyQuiz:my-favorite-question.html.twig', array(
            'favoriteActive' => 'active',
            'user' => $user,
            'favoriteQuestions' => ArrayToolkit::index($favoriteQuestions, 'id'),
            'testpapers' => ArrayToolkit::index($myTestpapers, 'id'),
            'questions' => ArrayToolkit::index($questions, 'id'),
            'paginator' => $paginator
        ));
    }

	private function getQuestionService ()
	{
		return $this->getServiceKernel()->createService('Question.QuestionService');
	}

	private function getCourseService ()
	{
		return $this->getServiceKernel()->createService('Course.CourseService');
	}

    private function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Testpaper.TestpaperService');
    }
}