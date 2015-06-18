<?php

namespace Topxia\Api\Filter;

class CourseThreadFilter implements Filter
{
    //输出前的字段控制
    //查看权限,附带内容可以写在这里
    public function filter(array &$data)
    {
        $data['createdTime'] = date('c', $data['createdTime']);

        return $data;
    }

}

