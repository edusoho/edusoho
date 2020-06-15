<?php

namespace AppBundle\Controller\My;

use AppBundle\Controller\BaseController;
use Biz\ItemBankExercise\Service\ItemBankExerciseService;
use Codeages\Biz\ItemBank\ItemBank\Service\ItemBankService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;

class ItemBankExerciseController extends BaseController
{
    public function teachingAction(Request $request, $filter)
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是教师，不能查看此页面! ');
        }

        $conditions = [
            'teacherIds' => $user['id']
        ];

        $paginator = new Paginator(
            $request,
            $this->getItemBankExerciseService()->countCourses($conditions),
            10
        );

        $itemCourses = $this->getItemBankExerciseService()->searchCourses(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $itemCourses = array_map(
            function ($set) {
                $questionBankInfo = $this->getItemBankService()->getItemBank($set['questionBankId']);
                $set['assessmentNum'] = $questionBankInfo['assessment_num'];
                $set['itemNum'] = $questionBankInfo['item_num'];
                return $set;
            }
            , $itemCourses);

        return $this->render('my/teaching/item-bank-exercise.html.twig', array(
            'courses' => $itemCourses,
            'paginator' => $paginator,
            'filter' => $filter
        ));
    }

    /**
     * @return ItemBankExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->getBiz()->service('ItemBankExercise:ItemBankExerciseService');
    }

    /**
     * @return ItemBankService
     */
    protected function getItemBankService()
    {
        return $this->createService('ItemBank:ItemBank:ItemBankService');
    }
}
