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

    public function infoAction(Request $request, $exerciseId)
    {
        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $data = $this->prepareExpiryMode($data);

            $this->getExerciseService()->updateBaseInfo($exerciseId, $data);

            return $this->createJsonResponse(true);
        }

        $exercise = $this->formatExerciseDate($exercise);

        return $this->render(
            'item-bank-exercise/exercise-set/base-info/marketing.html.twig',
            [
                'exercise' => $exercise,
                'questionBank' => $this->getQuestionBankService()->getQuestionBank($exercise['questionBankId']),
            ]
        );
    }

    protected function formatExerciseDate($exercise)
    {
        if (!empty($exercise['expiryStartDate'])) {
            $exercise['expiryStartDate'] = date('Y-m-d', $exercise['expiryStartDate']);
        }
        if (!empty($exercise['expiryEndDate'])) {
            $exercise['expiryEndDate'] = date('Y-m-d', $exercise['expiryEndDate']);
        }
        if ('end_date' == $exercise['expiryMode']) {
            $exercise['deadlineType'] = 'end_date';
            $exercise['expiryMode'] = 'days';
        }

        return $exercise;
    }

    public function prepareExpiryMode($data)
    {
        if (empty($data['expiryMode']) || 'days' != $data['expiryMode']) {
            unset($data['deadlineType']);
        }
        if (!empty($data['deadlineType'])) {
            if ('end_date' == $data['deadlineType']) {
                $data['expiryMode'] = 'end_date';
                if (isset($data['deadline'])) {
                    $data['expiryEndDate'] = $data['deadline'];
                }
            } else {
                $data['expiryMode'] = 'days';
            }
            unset($data['deadlineType']);
        }
        unset($data['deadline']);

        return $data;
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
