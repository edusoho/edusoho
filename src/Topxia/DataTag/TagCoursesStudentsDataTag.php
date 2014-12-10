<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;
use Topxia\Service\Common\ServiceKernel;

class TagCoursesStudentsDataTag extends BaseDataTag  implements DataTag  
{

    /**
     * 获取标签下所有课程的学习人数
     *
     * 可传入的参数：
     *   TagId 必需 标签ID
     * 
     * @param  array $arguments 参数
     * @return array 标签下所有的学生数量
     */
    public function getData(array $arguments)
    {	
              if(empty($arguments['tagId'])){
               return  0;
        }
        $result = $this->getTagCourseService()->getCourseStudentCountByTagIdAndCourseStatus($arguments['tagId'],null);
      
        return  $result;

        
    }

    protected function getTagCourseService()
    {
        return $this->getServiceKernel()->createService('Custom:TagCourse.TagCourseService');
    }

}
