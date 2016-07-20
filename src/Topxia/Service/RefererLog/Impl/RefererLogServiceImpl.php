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
        $user                            = $this->getCurrentUser();
        list($refererHost, $refererName) = $this->prepareRefererUrl($refererlog['refererUrl'], $refererlog['userAgent']);

        $refererlog['refererHost']   = $refererHost;
        $refererlog['refererName']   = $refererName;
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

    public function findRefererLogsGroupByTargetId($targetType, $orderBy, $startTime, $endTime, $start, $limit)
    {
        return $this->getRefererLogDao()->findRefererLogsGroupByTargetId($targetType, $orderBy, $startTime, $endTime, $start, $limit);
    }

    public function analysisSummary($conditions)
    {
        $analysisSummary = $this->getRefererLogDao()->analysisSummary($conditions);
        return $this->prepareAnalysisSummary($analysisSummary);
    }

    public function searchAnalysisSummaryList($conditions, $groupBy, $start, $limit)
    {
        $analysisSummaryList = $this->getRefererLogDao()->searchAnalysisSummaryList($conditions, $groupBy, $start, $limit);
        $totalCount          = array_sum(ArrayToolkit::column($analysisSummaryList, 'count'));
        return array_map(function ($referelog) use ($totalCount) {
            $referelog['percent']      = empty($totalCount) ? '0%' : round($referelog['count'] / $totalCount * 100, 2).'%';
            $referelog['orderPercent'] = empty($referelog['count']) ? '0%' : round($referelog['orderCount'] / $referelog['count'] * 100, 2).'%';
            return $referelog;
        }, $analysisSummaryList);
    }

    public function searchAnalysisDetailListCount($conditions)
    {
        return $this->getRefererLogDao()->searchAnalysisDetailListCount($conditions);
    }

    public function countDitinctLogsByField($conditions, $field)
    {
        return $this->getRefererLogDao()->countDitinctLogsByField($conditions, $field);
    }

    protected function prepareConditions($conditions)
    {
        return $conditions;
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
        $timeRangeRefererLogs     = $this->searchRefererLogs($conditions, array('createdTime', 'ASC'), 0, $timeRangeRefererLogCount);
        $timeRangeRefererLogs     = array_map(function ($log) {
            $log['createdTime'] = date("Y-m-d", $log['createdTime']);
            return $log;
        }, $timeRangeRefererLogs);

        return ArrayToolkit::group($timeRangeRefererLogs, 'createdTime');
    }

    protected function getRefererLogDao()
    {
        return $this->createDao('RefererLog.RefererLogDao');
    }

    private function prepareAnalysisSummary($refererlogDatas)
    {
        if (empty($refererlogDatas)) {
            return array();
        }
        $lenght = 6;

        $analysisDatas      = array_slice($refererlogDatas, 0, $lenght);
        $otherAnalysisDatas = count($refererlogDatas) >= $lenght ? array_slice($refererlogDatas, $lenght) : array();

        $totalCount           = array_sum(ArrayToolkit::column($refererlogDatas, 'count'));
        $othertotalCount      = array_sum(ArrayToolkit::column($otherAnalysisDatas, 'count'));
        $otherOrdertotalCount = array_sum(ArrayToolkit::column($otherAnalysisDatas, 'orderCount'));
        if (!empty($otherAnalysisDatas)) {
            array_push($analysisDatas, array('count' => $othertotalCount, 'orderCount' => $otherOrdertotalCount, 'refererName' => '其他'));
        }

        return array_map(function ($data) use ($totalCount) {
            $data['percent']      = empty($totalCount) ? '0%' : round($data['count'] / $totalCount * 100, 2).'%';
            $data['orderPercent'] = empty($data['count']) ? '0%' : round($data['orderCount'] / $data['count'] * 100, 2).'%';
            return $data;
        }, $analysisDatas);
    }

    private function prepareRefererUrl($refererUrl, $userAgent)
    {
        $refererHost = null;
        $refererName = null;
        $outerVisit  = true;

        $host       = $this->getKernel()->getEnvVariable('host');
        $refererMap = $this->getRefererMap();

        $patten = '/^(https|http)?(:\/\/)?([^\/]+)/';
        preg_match($patten, $refererUrl, $matches);

        array_walk($matches, function ($value) use ($host, &$outerVisit) {
            if ($value == $host) {
                $outerVisit = false;
            }
        });
        //站外
        if ($outerVisit) {
            $refererHost = $refererName = $matches[0];
        } else {
            if (strpos($userAgent, 'MicroMessenger') === false) {
                $refererHost = $refererName = $refererUrl;
            } else {
                $refererHost = $refererName = 'mp.weixin.qq.com';
            }
        }

        $refererName = $this->arrayFind($refererMap, $refererHost);

        return array($refererHost, $refererName);
    }

    private function arrayFind($array, $existsKey)
    {
        foreach ($array as $key => $value) {
            if (($existsKey == $key) || (strpos($existsKey, $key) !== false)) {
                return $value;
            }
        }
        return $existsKey;
    }

    private function getRefererMap()
    {
        $host = $this->getKernel()->getEnvVariable('host');
        return array(
            'open/course/explore'  => '公开课列表',
            'open/course'          => '公开课详情页',
            'my/courses/favorited' => '收藏页面',
            $host                  => '首页',
            'baidu.com'            => '百度',
            'www.so.com'           => '360搜索',
            'www.sogou.com'        => '搜狗搜索',
            'mp.weixin.qq.com'     => '微信',
            'weibo.com'            => '微博'
        );
    }
}
