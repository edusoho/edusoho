<?php

namespace AppBundle\Controller\AdminV2\Teach;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\QuestionBank\Service\CategoryService;
use Biz\QuestionBank\Service\QuestionBankService;
use Symfony\Component\HttpFoundation\Request;

class ItemBankExerciseController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();
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
}