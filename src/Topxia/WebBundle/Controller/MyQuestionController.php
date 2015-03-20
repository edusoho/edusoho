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
        if ($request->getMethod() == 'POST') {
            $targetType = $request->query->get('targetType');
            $targetId = $request->query->get('targetId');
            $target = $targetType."-".$targetId;

            $user = $this->getCurrentUser();

            $favorite = $this->getQuestionService()->favoriteQuestion($id, $target, $user['id']);
        
            return $this->createJsonResponse(true);
        }
    }

    public function unFavoriteQuestionAction(Request $request ,$id)
    {
        if ($request->getMethod() == 'POST') {
            $targetType = $request->query->get('targetType');
            $targetId = $request->query->get('targetId');
            $target = $targetType."-".$targetId;

            $user = $this->getCurrentUser();

            $this->getQuestionService()->unFavoriteQuestion($id, $target, $user['id']);

            return $this->createJsonResponse(true);
        }
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

        $targets = $this->get('topxia.target_helper')->getTargets(ArrayToolkit::column($favoriteQuestions, 'target'));

        foreach ($favoriteQuestions as $key => $value) {
            if ($targets[$value['target']]['type'] == 'testpaper'){
                array_push($myTestpaperIds, $targets[$value['target']]['id']);
            }
        }

        $myTestpapers = $this->getTestpaperService()->findTestpapersByIds($myTestpaperIds);
 
        return $this->render('TopxiaWebBundle:MyQuiz:my-favorite-question.html.twig', array(
            'favoriteActive' => 'active',
            'user' => $user,
            'favoriteQuestions' => ArrayToolkit::index($favoriteQuestions, 'id'),
            'testpapers' => ArrayToolkit::index($myTestpapers, 'id'),
            'questions' => ArrayToolkit::index($questions, 'id'),
            'targets' => $targets,
            'paginator' => $paginator
        ));
    }

    public function previewAction (Request $request, $id)
    {
        $question = $this->getQuestionService()->getQuestion($id);

        $userId = $this->getCurrentUser()->id;

        if (empty($question)) {
            throw $this->createNotFoundException('题目不存在！');
        }

        $myFavorites = $this->getQuestionService()->findAllFavoriteQuestionsByUserId($userId);

        if (!in_array($question['id'], ArrayToolkit::column($myFavorites, 'questionId'))){
            throw $this->createAccessDeniedException('无权预览非本人收藏的题目!');
        }

        $item = array(
            'questionId' => $question['id'],
            'questionType' => $question['type'],
            'question' => $question
        );

        if ($question['type'] == 'material'){
            $questions = $this->getQuestionService()->findQuestionsByParentId($id);

            foreach ($questions as $value) {
                $items[] = array(
                    'questionId' => $value['id'],
                    'questionType' => $value['type'],
                    'question' => $value
                );
            }

            $item['items'] = $items;
        }

        $type = in_array($question['type'], array('single_choice', 'uncertain_choice')) ? 'choice' : $question['type'];
        $questionPreview = true;

        return $this->render('TopxiaWebBundle:QuizQuestionTest:question-preview-modal.html.twig', array(
            'item' => $item,
            'type' => $type,
            'questionPreview' => $questionPreview
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