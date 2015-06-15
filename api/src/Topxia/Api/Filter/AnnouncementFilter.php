<?php

namespace Topxia\Api\Filter;

class AnnouncementFilter implements Filter
{
	//输出前的字段控制
    //查看权限,附带内容可以写在这里
    public function filter(array &$data)
    {
        $data['createdTime'] = date('c', $data['createdTime']);
        $data['updatedTime'] = date('c', $data['updatedTime']);
        $data['startTime'] = date('c', $data['startTime']);
        $data['endTime'] = date('c', $data['endTime']);

        return $data;
    }

}

