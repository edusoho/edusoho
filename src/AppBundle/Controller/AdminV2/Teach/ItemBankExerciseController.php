<?php

namespace AppBundle\Controller\AdminV2\Teach;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\QuestionBank\Service\CategoryService;
use Biz\QuestionBank\Service\QuestionBankService;
use MarketingMallBundle\Biz\ProductMallGoodsRelation\Service\ProductMallGoodsRelationService;
use Symfony\Component\HttpFoundation\Request;

class ItemBankExerciseController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions = $this->filterConditions($conditions);
        $paginator = new Paginator(
            $this->get('request'),
            $this->getExerciseService()->count($conditions),
            20
        );
        $exercises = $this->getExerciseService()->search(
            $conditions,
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($exercises, 'creator'));
        $exercisesStatusNum = $this->getDifferentStatusExercisesNum($conditions);
        $questionBanks = $this->getQuestionBankService()->findQuestionBanksByIds(ArrayToolkit::column($exercises, 'questionBankId'));
        $questionBanks = ArrayToolkit::index($questionBanks, 'id');

        return $this->render(
            'admin-v2/teach/item-bank-exercise/index.html.twig',
            [
                'exercises' => $exercises,
                'paginator' => $paginator,
                'exercisesStatusNum' => $exercisesStatusNum,
                'users' => $users,
                'categoryTree' => $this->getCategoryService()->getCategoryTree(),
                'categories' => $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($exercises, 'categoryId')),
                'questionBanks' => $questionBanks,
            ]
        );
    }

    public function publishAction(Request $request, $id)
    {
        $exercise = $this->getExerciseService()->publishExercise($id);

        return $this->createJsonResponse($exercise);
    }

    public function closeAction(Request $request, $id)
    {
        $exercise = $this->getExerciseService()->closeExercise($id);

        return $this->createJsonResponse($exercise);
    }

    public function deleteAction(Request $request, $id)
    {
        $this->getExerciseService()->deleteExercise($id);

        return $this->createJsonResponse(true);
    }



    public function recommendAction(Request $request, $id)
    {
        $exercise = $this->getExerciseService()->get($id);

        if ('POST' == $request->getMethod()) {
            $number = $request->request->get('number');

            $exercise = $this->getExerciseService()->recommendExercise($id, $number);

            return $this->createJsonResponse($exercise);
        }

        return $this->render(
            'admin-v2/teach/item-bank-exercise/recommend-modal.html.twig',
            [
                'exercise' => $exercise,
            ]
        );
    }

    public function cancelRecommendAction(Request $request, $id)
    {
        $exercise = $this->getExerciseService()->cancelRecommendExercise($id);

        return $this->createJsonResponse($exercise);
    }

    protected function filterConditions($conditions)
    {
        if (!empty($conditions['creatorName'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['creatorName']);
            $conditions['creator'] = $user ? $user['id'] : -1;
        }

        if (!empty($conditions['categoryId'])) {
            $categoryIds = $this->getCategoryService()->findAllChildrenIdsByParentId($conditions['categoryId']);
            $categoryIds[] = $conditions['categoryId'];
            $conditions['categoryIds'] = $categoryIds;
            unset($conditions['categoryId']);
        }

        return $conditions;
    }

    public function checkEsProductCanDeleteAction(Request $request, $id)
    {
        $status = $this->getProductMallGoodsRelationService()->checkEsProductCanDelete([$id], 'questionBank');
        return $this->createJsonResponse(['status' => $status]);
    }

    protected function getDifferentStatusExercisesNum($conditions)
    {
        $total = $this->getExerciseService()->count($conditions);
        $published = $this->getExerciseService()->count(array_merge($conditions, ['status' => 'published']));
        $closed = $this->getExerciseService()->count(array_merge($conditions, ['status' => 'closed']));
        $draft = $this->getExerciseService()->count(array_merge($conditions, ['status' => 'draft']));

        return [
            'total' => empty($total) ? 0 : $total,
            'published' => empty($published) ? 0 : $published,
            'closed' => empty($closed) ? 0 : $closed,
            'draft' => empty($draft) ? 0 : $draft,
        ];
    }

    public function recommendListAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions = $this->filterConditions($conditions);
        $conditions['recommended'] = 1;

        $paginator = new Paginator(
            $this->get('request'),
            $this->getExerciseService()->count($conditions),
            20
        );

        $exercises = $this->getExerciseService()->search(
            $conditions,
            ['recommendedSeq' => 'ASC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($exercises, 'creator'));

        return $this->render(
            'admin-v2/teach/item-bank-exercise/exercise-recommend-list.html.twig',
            [
                'exercises' => $exercises,
                'users' => $users,
                'paginator' => $paginator,
                'categoryTree' => $this->getCategoryService()->getCategoryTree(),
                'categories' => $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($exercises, 'categoryId')),
            ]
        );
    }

    /**
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->createService('ItemBankExercise:ExerciseService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('QuestionBank:CategoryService');
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->createService('QuestionBank:QuestionBankService');
    }

    /**
     * @return ProductMallGoodsRelationService
     */
    private function getProductMallGoodsRelationService()
    {
        return $this->createService('MarketingMallBundle:ProductMallGoodsRelation:ProductMallGoodsRelationService');
    }
}
