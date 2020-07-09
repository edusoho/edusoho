<?php

namespace AppBundle\Controller\AdminV2\Teach;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\ItemBankExercise\Service\AssessmentExerciseService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\QuestionBank\Service\CategoryService;
use Biz\QuestionBank\Service\QuestionBankService;
use Symfony\Component\HttpFoundation\Request;

class ItemBankExerciseAnalysisController extends BaseController
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

        $questionBanks = $this->getQuestionBankService()->findQuestionBanksByIds(ArrayToolkit::column($exercises, 'questionBankId'));
        $questionBanks = ArrayToolkit::index($questionBanks, 'id');
        foreach ($exercises as &$exercise) {
            $exercise['assessment_num'] = $this->getAssessmentExerciseService()->count(['exerciseId' => $exercise['id']]);
        }

        return $this->render(
            'admin-v2/teach/item-bank-exercise/analysis.html.twig',
            [
                'exercises' => $exercises,
                'paginator' => $paginator,
                'categoryTree' => $this->getCategoryService()->getCategoryTree(),
                'categories' => $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($exercises, 'categoryId')),
                'questionBanks' => $questionBanks,
            ]
        );
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
     * @return AssessmentExerciseService
     */
    protected function getAssessmentExerciseService()
    {
        return $this->createService('ItemBankExercise:AssessmentExerciseService');
    }
}
