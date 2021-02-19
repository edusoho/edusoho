<?php

namespace Biz\TrainingPlatform\Data;

use Biz\TrainingPlatform\Client\AbstractCloudAPI;

/**
 * 数据集相关接口
 */
class Base
{
    public $return = ['status'=>['code'=>5000000,'message'=>'失败'],'body'=>[]];
    public function __construct(){}
}