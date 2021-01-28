<?php

namespace Topxia\Api\Convert;
use Topxia\Service\Common\ServiceKernel;

class BlacklistConvert implements Convert
{
    //根据id等参数获取完整数据
    public function convert($id)
    {
        $blacklist = ServiceKernel::instance()->createService('User:BlacklistService')->getBlacklist($id);
        if (empty($blacklist)) {
            throw new \Exception('blacklist not found');
        }
        return $blacklist;
    }

}

