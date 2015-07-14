<?php

namespace Topxia\Api\Filter;
use Topxia\Service\Common\ServiceKernel;
class NotificationFilter implements Filter
{
	//输出前的字段控制
    //查看权限,附带内容可以写在这里
    public function filter(array &$data)
    {
        unset($data['isRead']);

        $data['createdTime'] = date('c', $data['createdTime']);
        return $data;
    }

    public function filters(array &$datas)
    {
        $num = 0;
        $results = array();
        foreach ($datas as $data) {
            $results[$num] = $this->filter($data);
            $num++;
        }
        return $results;
    }

}

