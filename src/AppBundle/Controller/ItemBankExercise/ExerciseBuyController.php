<?php

namespace AppBundle\Controller\ItemBankExercise;

use AppBundle\Controller\BuyFlowController;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Symfony\Component\HttpFoundation\Request;

class ExerciseBuyController extends BuyFlowController
{
    protected $targetType = 'item_bank_exercise';

    /**
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @todo 商品剥离控制购买入口需要改造
     */
    public function buyAction(Request $request, $id)
    {
        if (!$this->getCurrentUser()->isLogin()) {
            return $this->render('login/ajax.html.twig', ['_target_path' => $this->getTargetPath($request)]);
        }

        if ($this->needUploadAvatar()) {
            return $this->render('buy-flow/avatar-alert-modal.html.twig');
        }

        if ($this->needFillUserInfo()) {
            return $this->render('buy-flow/fill-user-info-modal.html.twig', $this->getUserFieldsAndUserInfo());
        }

        if ($this->needOpenPayment($id)) {
            return $this->render('buy-flow/payments-disabled-modal.html.twig');
        }

        $this->tryFreeJoin($id);

        if ($this->isJoined($id)) {
            $event = $this->needInformationCollectionAfterJoin($id);
            if ('POST' === $request->getMethod()) {
                return !empty($event) ? $this->createJsonResponse(['url' => $event['url']]) : $this->createJsonResponse(['url' => $this->getSuccessUrl($id)]);
            }

            return !empty($event) ? $this->redirect($event['url']) : $this->redirect($this->getSuccessUrl($id));
        }

        return $this->createJsonResponse(['url' => $this->generateUrl('order_show', ['targetId' => $id, 'targetType' => $this->targetType])]);
    }

    private function getUserFieldsAndUserInfo()
    {
        $userFields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();
        $user = $this->getCurrentUser();
        $userInfo = $this->getUserService()->getUserProfile($user['id']);
        $userInfo['approvalStatus'] = $user['approvalStatus'];

        $params['userFields'] = $userFields;
        $params['user'] = $userInfo;

        return $params;
    }

    protected function getSuccessUrl($id)
    {
        return $this->generateUrl('my_item_bank_exercise_show', ['id' => $id]);
    }

    protected function isJoined($id)
    {
        return $this->getExerciseMemberService()->isExerciseStudent($id, $this->getUser()->getId());
    }

    protected function tryFreeJoin($id)
    {
        $this->getExerciseService()->freeJoinExercise($id);
    }

    protected function needInformationCollectionBeforeJoin($targetId)
    {
        return [];
    }

    protected function needInformationCollectionAfterJoin($targetId)
    {
        return [];
    }

    /**
     * @return ExerciseService
     */
    private function getExerciseService()
    {
        return $this->createService('ItemBankExercise:ExerciseService');
    }

    /**
     * @return ExerciseMemberService
     */
    private function getExerciseMemberService()
    {
        return $this->createService('ItemBankExercise:ExerciseMemberService');
    }
}
