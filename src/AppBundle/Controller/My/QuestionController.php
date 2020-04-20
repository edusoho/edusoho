<?php

namespace AppBundle\Controller\My;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;
use Biz\Question\QuestionException;
use Symfony\Component\HttpFoundation\Request;
use Codeages\Biz\ItemBank\Item\Service\ItemFavoriteService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

class QuestionController extends BaseController
{
    public function favoriteListAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '请先登录！', '', 5, $this->generateUrl('login'));
        }

        $conditions = array(
            'user_id' => $user['id'],
            'target_type' => 'assessment',
        );

        $paginator = new Paginator(
            $request,
            $this->getItemFavoriteService()->count($conditions),
            10
        );

        $favoriteItems = $this->getItemFavoriteService()->search(
            $conditions,
            array('created_time' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $items = $this->getItemService()->findItemsByIds(ArrayToolkit::column($favoriteItems, 'item_id'));
        $assessments = $this->getAssessmentService()->findAssessmentsByIds(
            ArrayToolkit::column($favoriteItems, 'target_id')
        );

        return $this->render('my/question/favorite-list.html.twig', array(
            'favoriteItems' => $favoriteItems,
            'paginator' => $paginator,
            'items' => $items,
            'nav' => 'questionFavorite',
            'assessments' => $assessments,
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

            $this->getItemFavoriteService()->delete($id);

            return $this->createJsonResponse(array('result' => true, 'message' => ''));
        }
    }

    public function previewAction(Request $request, $id)
    {
        $user = $this->getUser();

        $conditions = array(
            'user_id' => $user['id'],
            'target_type' => 'assessment',
        );

        $favoriteItems = $this->getItemFavoriteService()->search(
            $conditions,
            array('created_time' => 'DESC'),
            0,
            $this->getItemFavoriteService()->count($conditions)
        );
        $favoriteItems = ArrayToolkit::index($favoriteItems, 'item_id');

        if (empty($favoriteItems[$id])) {
            $this->createNewException(QuestionException::FORBIDDEN_PREVIEW_QUESTION());
        }

        $item = $this->getItemService()->getItemWithQuestions($id, true);

        if (empty($item)) {
            $this->createNewException(QuestionException::NOTFOUND_QUESTION());
        }

        return $this->render('question-manage/preview-modal.html.twig', array(
            'item' => $item,
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

    /**
     * @return ItemFavoriteService
     */
    protected function getItemFavoriteService()
    {
        return $this->createService('ItemBank:Item:ItemFavoriteService');
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
