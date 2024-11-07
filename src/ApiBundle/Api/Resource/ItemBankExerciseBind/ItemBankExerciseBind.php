<?php

namespace ApiBundle\Api\Resource\ItemBankExerciseBind;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\MemberService;
use Biz\ItemBankExercise\Service\AssessmentExerciseService;
use Biz\ItemBankExercise\Service\ChapterExerciseService;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
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
            $exercise = $this->getItemBankExerciseService()->get($bindExercise['itemBankExerciseId']);
            $bindExercise['itemBankExercise'] = $itemBankExercises[$bindExercise['itemBankExerciseId']] ?? null;
            $bindExercise['chapterExerciseNum'] = $this->getChapterExerciseNum($exercise);
            $bindExercise['assessmentNum'] = $this->getAssessmentNum($exercise);
            $bindExercise['operateUser'] = $this->getUserService()->getUser($bindExercise['operatorId']);
        }

        return $bindExercises;
    }

    protected function getAssessmentNum($exercise)
    {
        if (!$exercise['assessmentEnable']) {
            return 0;
        }
        $modules = $this->getExerciseModuleService()->findByExerciseIdAndType($exercise['id'], 'assessment');
        $moduleIds = ArrayToolkit::column($modules, 'id');

        return $this->getAssessmentExerciseService()->count(['moduleIds' => $moduleIds]);
    }

    protected function getChapterExerciseNum($exercise)
    {
        if (!$exercise['chapterEnable']) {
            return 0;
        }
        $chapterTreeList = $this->getItemBankChapterExerciseService()->getChapterTreeList($exercise['questionBankId']);

        return count($chapterTreeList);
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

    /**
     * @return ExerciseModuleService
     */
    protected function getExerciseModuleService()
    {
        return $this->service('ItemBankExercise:ExerciseModuleService');
    }

    /**
     * @return AssessmentExerciseService
     */
    protected function getAssessmentExerciseService()
    {
        return $this->service('ItemBankExercise:AssessmentExerciseService');
    }

    /**
     * @return ChapterExerciseService
     */
    protected function getItemBankChapterExerciseService()
    {
        return $this->service('ItemBankExercise:ChapterExerciseService');
    }
}
