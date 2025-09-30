<?php

namespace AppBundle\Controller\ItemBankExercise;

use AppBundle\Controller\BaseController;
use Biz\Contract\Service\ContractService;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\QuestionBank\Service\CategoryService;
use Biz\QuestionBank\Service\QuestionBankService;
use Symfony\Component\HttpFoundation\Request;

class ExerciseManageController extends BaseController
{
    public function baseAction(Request $request, $exerciseId, $questionBankId = 0)
    {
        if ($questionBankId && 0 == $exerciseId) {
            return $this->forward('AppBundle:ItemBankExercise/Exercise:open', [
                'request' => $request,
                'id' => $questionBankId,
            ]);
        }

        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);
        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();
            if (!empty($data['covers'])) {
                $exercise = $this->getExerciseService()->changeExerciseCover($exercise['id'], json_decode($data['covers'], true));
            }
        }

        return $this->render(
            'item-bank-exercise-manage/exercise-set/info.html.twig',
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

        return $this->render('item-bank-exercise-manage/exercise-set/cover-crop-modal.html.twig');
    }

    public function infoAction(Request $request, $exerciseId)
    {
        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            if (isset($data['status']) && !$exercise['chapterEnable'] && !$exercise['assessmentEnable']) {
                $this->createNewException(ItemBankExerciseException::FORBIDDEN_TO_SAVE());
            }
            $data = $this->prepareExpiryMode($data);
            $contractEnable = $data['contractEnable'] ?? null;
            $contractId = $data['contractId'] ?? null;
            $contractForceSign = $data['contractForceSign'] ?? null;

            // 从 $data 中移除 contractEnable, contractId, contractForceSign
            unset($data['contractEnable'], $data['contractId'], $data['contractForceSign']);
            $this->getExerciseService()->updateBaseInfo($exerciseId, $data);
            if (empty($contractEnable)) {
                $this->getContractService()->unRelateContract("itemBankExercise_{$exerciseId}");
            } else {
                $this->getContractService()->relateContract($contractId, "itemBankExercise_{$exerciseId}", $contractForceSign);
            }

            return $this->createJsonResponse(true);
        }

        $exercise = $this->formatExerciseDate($exercise);
        $relatedContract = $this->getContractService()->getRelatedContractByGoodsKey("itemBankExercise_{$exerciseId}");
        $exercise['contractId'] = $relatedContract['contractId'] ?? 0;
        $exercise['contractForceSign'] = empty($relatedContract['sign']) ? 0 : 1;
        $exercise['contractName'] = $relatedContract['contractName'] ?? '';

        return $this->render(
            'item-bank-exercise-manage/exercise-set/base-info/marketing.html.twig',
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

    public function canOpenAction(Request $request, $exerciseId, $type)
    {
        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);

        if (!$exercise['assessmentEnable'] && !$exercise['chapterEnable']) {
            return $this->createJsonResponse(true);
        }

        if ('chapter' == $type) {
            $can = 1 == $exercise['assessmentEnable'] ? true : false;
        } else {
            $can = 1 == $exercise['chapterEnable'] ? true : false;
        }

        return $this->createJsonResponse($can);
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
     * @return ContractService
     */
    private function getContractService()
    {
        return $this->createService('Contract:ContractService');
    }
}
