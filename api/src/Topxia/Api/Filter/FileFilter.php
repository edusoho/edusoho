<?php

namespace Topxia\Api\Filter;
use Topxia\Service\Common\ServiceKernel;
class FileFilter implements Filter
{
	//输出前的字段控制
    //查看权限,附带内容可以写在这里
    public function filter(array &$data)
    {
        unset($data['groupId']);
        unset($data['mime']);
        unset($data['status']);
        unset($data['file']);
        $fileService = ServiceKernel::instance()->createService('Content:FileService');

        $data['createdTime'] = date('c', $data['createdTime']);
        $uri = empty($data['uri']) ? '' : $fileService->parseFileUri($data['uri']);
        $data['uri'] = empty($uri) ? '' : 'files/'.$uri['path'];
        
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

