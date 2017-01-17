<?php

namespace AppBundle\Controller\My;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;

class QuestionController extends BaseController
{
    public function favoriteListAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '请先登录！', '', 5, $this->generateUrl('login'));
        }

        $conditions = array(
            'userId' => $user['id']
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
        $questions   = $this->getQuestionService()->findQuestionsByIds($questionIds);

        return $this->render('my/question/favorite-list.html.twig', array(
            'favoriteQuestions' => $favoriteQuestions,
            'paginator'         => $paginator,
            'questions'         => $questions,
            'nav'               => 'questionFavorite'
        ));
    }

    public function favoriteAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $user = $this->getUser();

            if (!$user->isLogin()) {
                return $this->createJsonResponse(array('result' => false, 'message' => 'noLogin'));
            }

            $fields = $request->request->all();

            $this->getQuestionService()->createFavoriteQuestion($fields);

            return $this->createJsonResponse(array('result' => true, 'message' => ''));
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

        $myFavorite = $this->getQuestionService()->getUserFavoriteByQuestionId($user['id'], $question['id']);

        if (!$myFavorite) {
            throw new AccessDeniedException('Question preview access denied');
        }

        $question = $this->getQuestionService()->getQuestion($id);

        if (empty($question)) {
            throw new NotFoundException('Question not found');
        }

        if ($question['subCount'] > 0) {
            $questionSubs = $this->getQuestionService()->findQuestionsByParentId($question['id']);

            $question['subs'] = $questionSubs;
        }

        return $this->render('question-manage/preview-modal.html.twig', array(
            'question'     => $question,
            'showAnswer'   => 1,
            'showAnalysis' => 1
        ));
    }

    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }
}
