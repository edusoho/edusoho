<?php


namespace AppBundle\Controller\ItemBankExercise;


use AppBundle\Controller\BaseController;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\QuestionBank\Service\CategoryService;
use Biz\QuestionBank\Service\QuestionBankService;
use Symfony\Component\HttpFoundation\Request;

class ExerciseManageController extends BaseController
{
    public function baseAction(Request $request, $exerciseId)
    {
        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->getExerciseService()->updateBaseInfo($exercise['id'], $data);

            return $this->createJsonResponse(true);
        }

        return $this->render(
            'item-bank-exercise/exercise-set/info.html.twig',
            [
                'exercise' => $exercise,
                'questionBank' => $this->getQuestionBankService()->getQuestionBank($exercise['questionBankId']),
                'categoryTree' => $this->getCategoryService()->getCategoryTree(),
            ]
        );
    }

    public function coverCropAction(Request $request, $exerciseId)
    {
        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);

        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();
            $exercise = $this->getExerciseService()->changeExerciseCover($exercise['id'], $data['images']);
            $cover = $this->getWebExtension()->getFpath($exercise['cover']['large']);

            return $this->createJsonResponse(['image' => $cover]);
        }

        return $this->render('item-bank-exercise/exercise-set/cover-crop-modal.html.twig');
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