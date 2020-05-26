<?php

namespace AppBundle\Controller\My;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\Question\QuestionException;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;
use Codeages\Biz\ItemBank\Item\Service\QuestionFavoriteService;
use Symfony\Component\HttpFoundation\Request;

class QuestionController extends BaseController
{
    public function favoriteListAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '请先登录！', '', 5, $this->generateUrl('login'));
        }

        $conditions = [
            'user_id' => $user['id'],
            'target_type' => 'assessment',
        ];

        $paginator = new Paginator(
            $request,
            $this->getQuestionFavoriteService()->count($conditions),
            10
        );

        $favoriteQuestions = $this->getQuestionFavoriteService()->search(
            $conditions,
            ['created_time' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $questions = $this->getItemService()->findQuestionsByQuestionIds(ArrayToolkit::column($favoriteQuestions, 'question_id'));
        $assessments = $this->getAssessmentService()->findAssessmentsByIds(
            ArrayToolkit::column($favoriteQuestions, 'target_id')
        );

        return $this->render('my/question/favorite-list.html.twig', [
            'favoriteQuestions' => $favoriteQuestions,
            'paginator' => $paginator,
            'questions' => $questions,
            'nav' => 'questionFavorite',
            'assessments' => $assessments,
        ]);
    }

    public function favoriteAction(Request $request, $questionId)
    {
        if ('POST' == $request->getMethod()) {
            $user = $this->getUser();

            if (!$user->isLogin()) {
                return $this->createJsonResponse(['result' => false, 'message' => 'noLogin']);
            }

            $fields = $request->request->all();

            $fields['questionId'] = $questionId;

            $favorite = $this->getQuestionService()->createFavoriteQuestion($fields);

            $cancelUrl = $this->generateUrl('my_question_unfavorite', ['id' => $favorite['id']]);

            return $this->createJsonResponse(['result' => true, 'message' => '', 'url' => $cancelUrl]);
        }
    }

    public function unFavoriteAction(Request $request, $id)
    {
        if ('POST' == $request->getMethod()) {
            $user = $this->getUser();

            if (!$user->isLogin()) {
                return $this->createJsonResponse(['result' => false, 'message' => 'noLogin']);
            }

            $this->getQuestionFavoriteService()->delete($id);

            return $this->createJsonResponse(['result' => true, 'message' => '']);
        }
    }

    public function previewAction(Request $request, $id)
    {
        $user = $this->getUser();

        $conditions = [
            'user_id' => $user['id'],
            'target_type' => 'assessment',
        ];

        $favoriteItems = $this->getQuestionFavoriteService()->search(
            $conditions,
            ['created_time' => 'DESC'],
            0,
            $this->getQuestionFavoriteService()->count($conditions)
        );
        $favoriteItems = ArrayToolkit::index($favoriteItems, 'item_id');

        if (empty($favoriteItems[$id])) {
            $this->createNewException(QuestionException::FORBIDDEN_PREVIEW_QUESTION());
        }

        $item = $this->getItemService()->getItemWithQuestions($id, true);

        if (empty($item)) {
            $this->createNewException(QuestionException::NOTFOUND_QUESTION());
        }

        return $this->render('question-manage/preview-modal.html.twig', [
            'item' => $item,
        ]);
    }

    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    /**
     * @return QuestionFavoriteService
     */
    protected function getQuestionFavoriteService()
    {
        return $this->createService('ItemBank:Item:QuestionFavoriteService');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->createService('ItemBank:Item:ItemService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->createService('ItemBank:Assessment:AssessmentService');
    }
}
