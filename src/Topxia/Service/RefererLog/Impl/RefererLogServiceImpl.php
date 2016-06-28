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

        list($refererHost, $refererName) = $this->prepareRefererUrl($refererlog['refererUrl']);

        $refererlog['refererHost']   = $refererHost;
        $refererlog['refererName']   = $refererName;
        $refererlog['createdUserId'] = $user['id'];

        return $this->getRefererLogDao()->addRefererLog($refererlog);
    }

    public function getRefererLogById($id)
    {
        return $this->getRefererLogDao()->getRefererLogById($id);
    }

    public function searchAnalysisSummary($conditions)
    {
        $refererlogDatas = $this->getRefererLogDao()->searchAnalysisSummary($conditions);
        if (empty($refererlogDatas)) {
            return array();
        }
        //列表显示前六条
        $length = 6;

        $analysisDatas      = array_slice($refererlogDatas, 0, $length);
        $otherAnalysisDatas = count($refererlogDatas) > $length ? array_slice($refererlogDatas, $length) : array();

        $totalCount      = array_sum(ArrayToolkit::column($refererlogDatas, 'count'));
        $othertotalCount = array_sum(ArrayToolkit::column($otherAnalysisDatas, 'count'));

        array_push($analysisDatas, array('count' => $othertotalCount, 'refererName' => '其他'));
        $analysisDatas = array_map(
            function ($data) use ($totalCount) {
                $data['percent'] = empty($totalCount) ? '0%' : round($data['count'] / $totalCount * 100, 2).'%';
                return $data;
            }, $analysisDatas);
        return $analysisDatas;
    }

    public function searchAnalysisSummaryList($conditions, $groupBy, $start, $limit)
    {
        return $this->getRefererLogDao()->searchAnalysisSummaryList($conditions, $groupBy, $start, $limit);
    }

    public function searchAnalysisDetail($conditions, $groupBy)
    {
        $AnalysisDetail = $this->getRefererLogDao()->searchAnalysisDetail($conditions, $groupBy);
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

    private function prepareRefererUrl($refererUrl)
    {
        $refererHost = null;
        $refererName = null;

        $refererMap = $this->getRefererMap();
        $host       = $this->getKernel()->getEnvVariable('host');
        //站外
        if (strpos($refererUrl, $host) === false) {
            $patten = '/^(https|http)?(:\/\/)?([^\/]+)/';
            preg_match($patten, $refererUrl, $matches);
            $refererHost = $refererName = $matches[0];
        } else {
            $refererHost = $refererName = $refererUrl;
        }
        $refererName = $this->arrayFind($refererMap, $refererUrl);
        return array($refererHost, $refererName);
    }

    private function arrayFind($array, $existsKey)
    {
        foreach ($array as $key => $value) {
            if (($existsKey == $key) || (strpos($existsKey, $key) !== false)) {
                return $value;
            }
        }
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
            'mp.weixin.qq.com'     => '微信',
            'weibo.com'            => '微博'
        );
    }
}
