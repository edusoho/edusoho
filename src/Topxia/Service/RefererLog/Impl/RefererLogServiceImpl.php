<?php
namespace Topxia\Service\RefererLog\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\RefererLog\RefererLogService;

class RefererLogServiceImpl extends BaseService implements RefererLogService
{
    public function addRefererLog($refererlog)
    {
        if (!ArrayToolkit::requireds($refererlog, array('targetId', 'targetType', 'refererUrl'))) {
            throw $this->createServiceException("缺少字段添加RefererLog,增加失败");
        }
        if (!in_array($refererlog['targetType'], array('course', 'openCourse', 'classroom', 'vip'))) {
            throw $this->createServiceException("模块 {$refererlog['targetType']} 不允许添加RefererLog");
        }

        $user                        = $this->getCurrentUser();
        $refererlog['refererHost']   = $this->prepareRefererUrl($refererlog['refererUrl']);
        $refererlog['createdUserId'] = $user['id'];
        return $this->getRefererLogDao()->addRefererLog($refererlog);
    }

    public function getRefererLogById($id)
    {
        return $this->getRefererLogDao()->getRefererLogById($id);
    }

    public function waveRefererLog($id, $field, $diff)
    {
        return $this->getRefererLogDao()->waveRefererLog($id, $field, $diff);
    }

    public function searchRefererLogs($conditions, $orderBy, $start, $limit)
    {
        return $this->getRefererLogDao()->searchRefererLogs($conditions, $orderBy, $start, $limit);
    }

    public function searchRefererLogCount($conditions)
    {
        return $this->getRefererLogDao()->searchRefererLogCount($conditions);
    }

    public function searchRefererLogsGroupByTargetId($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->prepareConditions($conditions);
        return $this->getRefererLogDao()->searchRefererLogsGroupByTargetId($conditions, $orderBy, $start, $limit);
    }

    public function searchRefererLogCountGroupByTargetId($conditions)
    {
        $conditions = $this->prepareConditions($conditions);
        return $this->getRefererLogDao()->searchRefererLogCountGroupByTargetId($conditions);
    }

    protected function prepareConditions($conditions)
    {
        if (!empty($conditions['startDate'])) {
            $conditions['startTime'] = strtotime($conditions['startDate']);
            unset($conditions['startDate']);
        }

        if (!empty($conditions['endDate'])) {
            $conditions['endTime'] = strtotime($conditions['endDate']);
            unset($conditions['endDate']);
        }

        return $conditions;
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
