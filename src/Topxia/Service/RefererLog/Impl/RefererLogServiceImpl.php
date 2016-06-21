<?php
namespace Topxia\Service\RefererLog\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\RefererLog\ReferLogService;

class RefererLogServiceImpl extends BaseService implements ReferLogService
{
    public function addRefererLog($targertId, $targertType, $refererUrl)
    {
        if (in_array($targertType, array('course', 'open_course', 'classroom', 'vip'))) {
            throw $this->createServiceException("模块 {$targertType} 不允许添加RefererLog");
        }
        $user       = $this->getCurrentUser();
        $refererlog = array(
            'targertId'     => $targertId,
            'targertType'   => $targertType,
            'refererUrl'    => $refererUrl,
            'refererHost'   => $this->prepareRefererUrl($refererUrl),
            'createdTime'   => time(),
            'createdUserId' => $user['id']
        );
        return $this->getRefererLogDao()->addRefererLog($refererlog);
    }

    public function getRefererLogById($id)
    {
        return $this->getRefererLogDao()->getRefererLogDao($id);
    }

    protected function getRefererLogDao()
    {
        return $this->createDao('RefererLog.RefererLogDao');
    }

    private function prepareRefererUrl($refererUrl)
    {
        $refererMap = array(
            'baidu.com'        => '百度',
            'mp.weixin.qq.com' => '微信',
            'weibo.com'        => '微博'
        );
        $patten = '/^(https|http)?(:\/\/)?([^\/]+)/';
        preg_match($patten, $refererUrl, $matches);
        $refererHost = $matches[0];
        //  TODO $refererName = array
        return $refererHost;
    }
}
