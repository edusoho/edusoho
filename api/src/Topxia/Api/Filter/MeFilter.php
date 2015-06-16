<?php

namespace Topxia\Api\Filter;

class MeFilter implements Filter
{
	//输出前的字段控制
    //查看权限,附带内容可以写在这里
    public function filter(array &$data)
    {
        if (empty($data['id'])) {
            return $data;
        }
        unset($data['password']);
        unset($data['salt']);
        unset($data['payPassword']);
        unset($data['payPasswordSalt']);
        $data['createdTime'] = date('c', $data['createdTime']);

        return $data;
    }

}

