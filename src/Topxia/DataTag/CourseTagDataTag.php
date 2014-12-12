<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;
use Topxia\Service\Common\ServiceKernel;

class CourseTagDataTag extends CourseBaseDataTag implements DataTag  
{

   
    /**
    * 获取课程的标签名称
    *
    */
    public function getData(array $arguments)
    {   

        if(empty($arguments['tagId'])){
               return  array();
        }

        return  $this->getTagService()->getTag($arguments['tagId']);

       

        // return $this->getCourseTeachersAndCategories($courses);
    }

     protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }


}
