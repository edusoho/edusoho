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
        $user = $this->getCurrentUser();

        list($refererHost, $refererName) = $this->prepareRefererUrl($refererlog['refererUrl'], $refererlog['schemeHost']);

        $refererlog['refererHost']   = $refererHost;
        $refererlog['refererName']   = $refererName;
        $refererlog['createdUserId'] = $user['id'];
        unset($refererlog['schemeHost']);

        return $this->getRefererLogDao()->addRefererLog($refererlog);
    }

    public function getRefererLogById($id)
    {
        return $this->getRefererLogDao()->getRefererLogById($id);
    }

    public function searchAnalysisSummary($conditions, $groupBy)
    {
        return $this->getRefererLogDao()->searchAnalysisSummary($conditions, $groupBy);
    }

    public function searchAnalysisSummaryList($conditions, $groupBy, $start, $limit)
    {
        return $this->getRefererLogDao()->searchAnalysisSummaryList($conditions, $groupBy, $start, $limit);
    }

    public function searchAnalysisDetail($conditions, $groupBy)
    {
        return $this->getRefererLogDao()->searchAnalysisDetail($conditions, $groupBy);
    }

    public function searchAnalysisDetailList($conditions, $groupBy, $start, $limit)
    {
        return $this->getRefererLogDao()->searchAnalysisDetailList($conditions, $groupBy, $start, $limit);
    }

    public function searchAnalysisDetailListCount($conditions)
    {
        return $this->getRefererLogDao()->searchAnalysisDetailListCount($conditions);
    }

    public function searchAnalysisSummaryListCount($conditions)
    {
        return $this->getRefererLogDao()->searchAnalysisSummaryListCount($conditions);
    }

    public function searchRefererLogs($conditions, $orderBy, $start, $limit)
    {
        return $this->getRefererLogDao()->searchRefererLogs($conditions, $orderBy, $start, $limit);
    }

    public function searchRefererLogCount($conditions)
    {
        return $this->getRefererLogDao()->searchRefererLogCount($conditions);
    }

    public function findRefererLogsGroupByDate($conditions)
    {
        $timeRangeRefererLogCount = $this->searchRefererLogCount($conditions);
        $timeRangeRefererLogs = $this->searchRefererLogs($conditions, array('createdTime', 'ASC'), 0, $timeRangeRefererLogCount);
        $timeRangeRefererLogs = array_map(function($log){
            $log['createdTime'] = date("Y-m-d", $log['createdTime']);
            return $log;
        }, $timeRangeRefererLogs);

        return ArrayToolkit::group($timeRangeRefererLogs, 'createdTime');
    }

    protected function getRefererLogDao()
    {
        return $this->createDao('RefererLog.RefererLogDao');
    }

    private function prepareRefererUrl($refererUrl, $schemeHost)
    {
        $refererHost = null;
        $refererName = null;

        $refererMap = array(
            'open/course/explore'  => '公开课列表',
            'open/course'          => '公开课详情页',
            'my/courses/favorited' => '收藏页面',
            $schemeHost            => '首页',
            'baidu.com'            => '百度',
            'mp.weixin.qq.com'     => '微信',
            'weibo.com'            => '微博'
        );

        //站外
        if (strpos($refererUrl, $schemeHost) === false) {
            $patten = '/^(https|http)?(:\/\/)?([^\/]+)/';
            preg_match($patten, $refererUrl, $matches);
            $refererHost = $matches[0];
        } else {
            $refererHost = $refererUrl;
        }

        foreach ($refererMap as $key => $value) {
            if (strpos($refererUrl, $key) !== false) {
                $refererName = $value;
                break;
            }
        }
        return array($refererHost, $refererName);
    }
}
