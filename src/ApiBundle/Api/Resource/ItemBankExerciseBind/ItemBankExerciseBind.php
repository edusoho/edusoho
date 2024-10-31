<?php

namespace ApiBundle\Api\Resource\ItemBankExerciseBind;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\MemberService;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\User\Service\UserService;

class ItemBankExerciseBind extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();
        $this->getItemBankExerciseService()->bindExercise($params['bindType'], $params['bindId'], $params['exerciseIds']);

        return ['success' => true];
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        $bindExercises = $this->getItemBankExerciseService()->findBindExercise($conditions['bindType'], $conditions['bindId']);
        $exerciseIds = array_values(array_unique(array_column($bindExercises, 'itemBankExerciseId')));
        $itemBankExercises = $this->getItemBankExerciseService()->findByIds($exerciseIds);
        foreach ($bindExercises as &$bindExercise) {
            $bindExercise['itemBankExercise'] = $itemBankExercises[$bindExercise['itemBankExerciseId']] ?? null;
            $bindExercise['chapterExerciseNum'] = 0;
            $bindExercise['assessmentNum'] = 0;
            $bindExercise['operateUser'] = $this->getUserService()->getUser(2);
        }
        // 绑定人
        // 章节练习数量

        // 试卷练习数量

        return $bindExercises;
    }

    public function remove(ApiRequest $request, $id)
    {
        $this->getItemBankExerciseService()->removeBindExercise($id);

        return ['success' => true];
    }

    /**
     * @return ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }

    /**
     * @return ExerciseMemberService
     */
    protected function getExerciseMemberService()
    {
        return $this->service('ItemBankExercise:ExerciseMemberService');
    }
}
