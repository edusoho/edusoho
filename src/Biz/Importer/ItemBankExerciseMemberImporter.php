<?php

namespace Biz\Importer;

use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Symfony\Component\HttpFoundation\Request;

class ItemBankExerciseMemberImporter extends Importer
{
    protected $type = 'exercise-member';

    public function import(Request $request)
    {
        $importData = $request->request->get('importData');
        $exerciseId = $request->request->get('exerciseId');
        $price = $request->request->get('price');
        $remark = $request->request->get('remark', '通过批量导入添加');
        $exercise = $this->getExerciseService()->get($exerciseId);
        $orderData = [
            'amount' => $price,
            'remark' => $remark,
        ];

        return $this->excelDataImporting($exercise, $importData, $orderData);
    }

    protected function excelDataImporting($exercise, $userData, $orderData)
    {
        $existsUserCount = 0;
        $successCount = 0;

        foreach ($userData as $key => $user) {
            if (!empty($user['nickname'])) {
                $user = $this->getUserService()->getUserByNickname($user['nickname']);
            } else {
                if (!empty($user['email'])) {
                    $user = $this->getUserService()->getUserByEmail($user['email']);
                } else {
                    $user = $this->getUserService()->getUserByVerifiedMobile($user['verifiedMobile']);
                }
            }

            $isExerciseMember = $this->getExerciseMemberService()->isExerciseMember($exercise['id'], $user['id']);

            if ($isExerciseMember) {
                ++$existsUserCount;
            } else {
                $data = [
                    'price' => $orderData['amount'],
                    'remark' => empty($orderData['remark']) ? '通过批量导入添加' : $orderData['remark'],
                    'source' => 'outside',
                ];
                $this->getExerciseMemberService()->becomeStudent($exercise['id'], $user['id'], $data);

                ++$successCount;
            }
        }

        return ['existsUserCount' => $existsUserCount, 'successCount' => $successCount];
    }

    public function getTemplate(Request $request)
    {
        $exerciseId = $request->query->get('exerciseId');
        $course = $this->getExerciseService()->get($exerciseId);

        return $this->render(
            'item-bank-exercise-manage/student-manage/import.html.twig',
            [
                'exercise' => $course,
                'importerType' => $this->type,
            ]
        );
    }

    public function tryImport(Request $request)
    {
        $exerciseId = $request->query->get('exerciseId');

        if (empty($exerciseId)) {
            $exerciseId = $request->request->get('exerciseId');
        }

        $this->getExerciseService()->tryManageExercise($exerciseId);
    }

    /**
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->biz->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return ExerciseMemberService
     */
    protected function getExerciseMemberService()
    {
        return $this->biz->service('ItemBankExercise:ExerciseMemberService');
    }
}
