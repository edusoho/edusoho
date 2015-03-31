<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class TeacherTagDataTag extends BaseDataTag implements DataTag  
{

    /**
     * 获取老师的标签，
     *
     * 可传入的参数：
     *   type   必需  标签类型  grade 年级标签 subject 学科标签
     * 
     * @param  array $arguments 参数
     * @return array 标签列表
     */

    public function getData(array $arguments)
    {	
        $this->checkType($arguments['type']);

        $total = $this->getTeacherTagService()->getTagCountByType($arguments['type']);

        return $this->getTeacherTagService()->findAllTagsByType(0,$total,$arguments['type']);

    }


    protected function getTeacherTagService()
    {
        return $this->getServiceKernel()->createService('Custom:Taxonomy.TagTeacherService');
    }


    protected function checkType($type)
    {
        if (empty($type)) {
            throw new \InvalidArgumentException("类型参数缺失");            
        }
        if($type != 'grade' && $type != 'subject'){
            throw new \InvalidArgumentException("类型参数错误");    
        }
    }

}
