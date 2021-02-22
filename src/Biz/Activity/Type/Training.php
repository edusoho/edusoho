<?php

namespace Biz\Activity\Type;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\TrainingActivityDao;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseDraftService;
use Biz\TrainingPlatform\Data\CourseCorrelation;

class Training extends Activity
{
    protected function registerListeners()
    {
        return array();
    }

    public function get($targetId)
    {
        // 获取绑定关系信息
        return [];
        // return $this->getTrainingActivityDao()->get($targetId);
    }

    public function find($ids, $showCloud = 1)
    {
        return [];
        // return $this->getTrainingActivityDao()->findByIds($ids);
    }

    public function copy($activity, $config = array())
    {
        return true;
        // $user = $this->getCurrentUser();
        // $text = $this->getTrainingActivityDao()->get($activity['mediaId']);
        // $newText = array(
        //     'finishType' => $text['finishType'],
        //     'finishDetail' => $text['finishDetail'],
        //     'createdUserId' => $user['id'],
        // );

        // return $this->getTrainingActivityDao()->create($newText);
    }

    public function sync($sourceActivity, $activity)
    {
        $sourceText = $this->getTrainingActivityDao()->get($sourceActivity['mediaId']);
        $text = $this->getTrainingActivityDao()->get($activity['mediaId']);
        $text['finishType'] = $sourceText['finishType'];
        $text['finishDetail'] = $sourceText['finishDetail'];

        return $this->getTrainingActivityDao()->update($text['id'], $text);
    }

    public function update($targetId, &$fields, $activity)
    {
        $training = ArrayToolkit::parts(
            $fields,
            array(
                'finishType',
                'finishDetail',
            )
        );

        $user = $this->getCurrentUser();
        $training['createdUserId'] = $user['id'];

        // 草稿后期删了
        $this->getCourseDraftService()->deleteCourseDrafts(
            $activity['fromCourseId'],
            $activity['id'],
            $user['id']
        );
        
        $ret = $this->getTrainingActivityDao()->update($targetId, $training);
        if($ret['id']){
            $params['id'] = $ret['id'];
            $params = $this->parseFields($fields);
            $result = $this->getCourseCorrelationObj()->update($params);
            if($result['status']['code'] == 2000000){
                return $ret;
            }else{
                return false;
            }
        }
    }

    public function delete($targetId)
    {
        return true;
        // return $this->getTrainingActivityDao()->delete($targetId);
    }

    public function create($fields)
    {
        $user = $this->getCurrentUser();
        $training['createdUserId'] = $user['id'];

        // 草稿后续去掉
        $this->getCourseDraftService()->deleteCourseDrafts($fields['fromCourseId'], 0, $user['id']);

        $ret = $this->getTrainingActivityDao()->create($training);
        // 设置关联关系
        if( $ret['id'] > 0 ){
            $params = $this->parseFields($fields);
            $result = $this->getCourseCorrelationObj()->create($params);
            if($result['status']['code'] == 2000000){
                return $ret;
            }else{
                return false;
            }
        }
        return false;
    }

    // 提交实训端参数梳理
    private function parseFields($fields=[]){
        $images = json_decode($fields['images'],true);
        $params = [
            "fromCourseSetId"   => $fields["fromCourseSetId"],
            "subsection_id"     => $ret['id'],
            "lab_type"          => $fields["lab_type"],
            "img_repo"          => $images["name"],
            "img_tag"           => $images["version"],
            "create_user_id"    => $user['id'],
        ];
        if($fields["lab_type"] == 1){
            $datasets = json_decode($fields["datasets"],true);
            $ids = [];
            foreach($datasets as $info){
                $ids[] = $info['id'];
            }
            $params["dataset_id"] = $ids;
        }elseif($fields["lab_type"] == 2){
            $params["link"] = $fields["link_url"];
        }
        return $params;
    }


    protected function getTrainingActivityDao()
    {
        return $this->getBiz()->dao('Activity:TrainingActivityDao');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return CourseDraftService
     */
    protected function getCourseDraftService()
    {
        return $this->getBiz()->service('Course:CourseDraftService');
    }
    protected function getCourseCorrelationObj(){
        return new CourseCorrelation();
    }
}
