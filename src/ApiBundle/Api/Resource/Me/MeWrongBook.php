<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseSetService;
use Biz\WrongBook\Service\WrongQuestionService;
use Codeages\Biz\ItemBank\ItemBank\Service\ItemBankService;

class MeWrongBook extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\WrongBook\WrongBookFilter", mode="public")
     */
    public function search(ApiRequest $request)
    {
        $userId = $this->getCurrentUser()->getId();
        $defaultWrongPools = [
            'course' => [
                'sum_wrong_num' => 0,
                'user_id' => $userId,
                'target_type' => 'course',
            ],
            'classroom' => [
                'sum_wrong_num' => 0,
                'user_id' => $userId,
                'target_type' => 'classroom',
            ],
            'exercise' => [
                'sum_wrong_num' => 0,
                'user_id' => $userId,
                'target_type' => 'exercise',
            ],
        ];
        $wrongPools = $this->getWrongQuestionService()->getWrongBookPoolByFields(['user_id' => $userId]);
        $wrongPools=ArrayToolkit::group($wrongPools,'target_type');
        $courseSetIds=isset($wrongPools['course'])?ArrayToolkit::column($wrongPools['course'], 'target_id'):[];
        $classroomIds=isset($wrongPools['classroom'])?ArrayToolkit::column($wrongPools['classroom'], 'target_id'):[];
        $exerciseIds=isset($wrongPools['exercise'])?ArrayToolkit::column($wrongPools['exercise'], 'target_id'):[];
        $newWrongPools['course']=$this->getSumNum($wrongPools,$courseSetIds,'course',$userId);
        $newWrongPools['classroom']=$this->getSumNum($wrongPools,$classroomIds,'classroom',$userId);
        $newWrongPools['exercise']=$this->getSumNum($wrongPools,$exerciseIds,'exercise',$userId);
        $wrongPools = array_merge($defaultWrongPools, $newWrongPools);

        return $wrongPools;
    }

    private function getSumNum($wrongPools,$ids,$type,$userId){
        $sumWrongNum=0;
        if(!empty($ids)){
            $datas=[];
            if($type==='course'){
                $datas=$this->getCourseService()->findCourseSetsByIds($ids);
            }elseif ($type==='classroom'){
                $datas=$this->getClassroomService()->findClassroomsByIds($ids);
            }elseif ($type==='exercise'){
                $datas=$this->getItemBankService()->searchItemBanks(['ids'=>$ids],[],0,PHP_INT_MAX);
            }

            $hasCourseSetIds=ArrayToolkit::column($datas, 'id');
            $differenceIds=array_diff($ids,$hasCourseSetIds);
            if(!empty($differenceIds)){
                $poolCourseSets=ArrayToolkit::index($wrongPools[$type], 'target_id');
                foreach ($differenceIds as $differenceId){
                    unset($poolCourseSets[$differenceId]);
                }
                $wrongPools[$type]=$poolCourseSets;
            }
            $sumWrongNum=array_sum(ArrayToolkit::column($wrongPools[$type], 'item_num'));
        }
        $newDatas['sum_wrong_num']=$sumWrongNum;
        $newDatas['user_id']=$userId;
        $newDatas['target_type']=$type;
        return $newDatas;
    }

    /**
     * @return WrongQuestionService
     */
    private function getWrongQuestionService()
    {
        return $this->service('WrongBook:WrongQuestionService');
    }

    /**
     * @return CourseSetService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseSetService');
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }

    /**
     * @return ItemBankService
     */
    protected function getItemBankService()
    {
        return $this->service('ItemBank:ItemBank:ItemBankService');
    }
}
