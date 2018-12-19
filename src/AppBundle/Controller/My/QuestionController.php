<?php

namespace AppBundle\Controller\My;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;
use Biz\Question\QuestionException;
use Symfony\Component\HttpFoundation\Request;

class QuestionController extends BaseController
{
    public function favoriteListAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '请先登录！', '', 5, $this->generateUrl('login'));
        }

        $conditions = array(
            'userId' => $user['id'],
        );

        $paginator = new Paginator(
            $request,
            $this->getQuestionService()->searchFavoriteCount($conditions),
            10
        );

        $favoriteQuestions = $this->getQuestionService()->searchFavoriteQuestions(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $questionIds = ArrayToolkit::column($favoriteQuestions, 'questionId');
        $questions = $this->getQuestionService()->findQuestionsByIds($questionIds);

        $testpaperIds = array();
        $testpaperIds = array_map(function ($favorite) {
            if ($favorite['targetType'] == 'testpaper') {
                return $favorite['targetId'];
            }
        }, $favoriteQuestions);

        $testpapers = $this->getTestpaperService()->findTestpapersByIds($testpaperIds);

        return $this->render('my/question/favorite-list.html.twig', array(
            'favoriteQuestions' => $favoriteQuestions,
            'paginator' => $paginator,
            'questions' => $questions,
            'nav' => 'questionFavorite',
            'testpapers' => $testpapers,
        ));
    }

    public function favoriteAction(Request $request, $questionId)
    {
        if ($request->getMethod() == 'POST') {
            $user = $this->getUser();

            if (!$user->isLogin()) {
                return $this->createJsonResponse(array('result' => false, 'message' => 'noLogin'));
            }

            $fields = $request->request->all();

            $fields['questionId'] = $questionId;

            $favorite = $this->getQuestionService()->createFavoriteQuestion($fields);

            $cancelUrl = $this->generateUrl('my_question_unfavorite', array('id' => $favorite['id']));

            return $this->createJsonResponse(array('result' => true, 'message' => '', 'url' => $cancelUrl));
        }
    }

    public function unFavoriteAction(Request $request, $id)
    {
        if ($request->getMethod() == 'POST') {
            $user = $this->getUser();

            if (!$user->isLogin()) {
                return $this->createJsonResponse(array('result' => false, 'message' => 'noLogin'));
            }

            $myFavorite = $this->getQuestionService()->getFavoriteQuestion($id);
            if (!$myFavorite) {
                return $this->createJsonResponse(array('result' => false, 'message' => 'favorite question not found'));
            }

            $this->getQuestionService()->deleteFavoriteQuestion($myFavorite['id']);

            return $this->createJsonResponse(array('result' => true, 'message' => ''));
        }
    }

    public function previewAction(Request $request, $id)
    {
        $user = $this->getUser();

        $userFavorites = $this->getQuestionService()->findUserFavoriteQuestions($user['id']);
        $userFavorites = ArrayToolkit::index($userFavorites, 'questionId');

        if (empty($userFavorites[$id])) {
            $this->createNewException(QuestionException::FORBIDDEN_PREVIEW_QUESTION());
        }

        $question = $this->getQuestionService()->get($id);

        if (empty($question)) {
            $this->createNewException(QuestionException::NOTFOUND_QUESTION());
        }

        if ($question['subCount'] > 0) {
            $questionSubs = $this->getQuestionService()->findQuestionsByParentId($question['id']);

            $question['subs'] = $questionSubs;
        }

        return $this->render('question-manage/preview-modal.html.twig', array(
            'question' => $question,
            'showAnswer' => 1,
            'showAnalysis' => 1,
        ));
    }

    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }
}
