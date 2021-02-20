<?php

namespace Biz\TrainingPlatform\Data;

use AppBundle\Common\Paginator;
use Biz\TrainingPlatform\Data\Base;
use Biz\TrainingPlatform\Client\AbstractCloudAPI;

/**
 * 关联关系接口
 */
class CourseCorrelation extends Base
{
    public $client;
    public $pageSize=5;
    const APILIST = [
        "bindResources"      =>  'tm/course/{course_id}/{subsection_id}/res',              // 获取课程绑定资源 
    ];
    public function __construct(){
        $this->client = new AbstractCloudAPI();
    }

    // 获取课程绑定资源
    public function getCourseBindResources($course_id,$subsection_id){
        $result = $this->client->get(str_replace(["{course_id}","{subsection_id}"],[$course_id,$subsection_id],self::APILIST['bindResources']));
        if($result['status']['code'] == 2000000){
            $this->return = $result;
        }
        return $this->return;
    }
    
}