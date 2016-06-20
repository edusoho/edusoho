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
            'createdTime'   => time(),
            'createdUserId' => $user['id']
        );
        $this->prepareRefererUrl($refererUrl);

        /*

    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `targertId` int(11) NOT NULL COMMENT '模块ID',
    `targertType` varchar(64) NOT NULL COMMENT '模块类型',
    `refererUrl`  varchar(255) DEFAULT '' COMMENT '访问来源Url',
    `refererHost` varchar(80)  DEFAULT '' COMMENT '访问来源HOST',
    `refererName` varchar(64)  DEFAULT '' COMMENT '访问来源站点名称',
    `orderCount` int(10) unsigned  DEFAULT '0'  COMMENT '促成订单数',
    `createdTime` int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '访问时间',
    `createdUserId` int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '访问者',*/
    }

    private function prepareRefererUrl($refererUrl)
    {
        $referer = explode('/', $refererUrl);
    }

    public function findRefererLogById($id)
    {
    }
}
