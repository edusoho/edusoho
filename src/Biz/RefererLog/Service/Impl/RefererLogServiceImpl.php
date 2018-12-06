<?php

namespace Biz\RefererLog\Service\Impl;

use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\RefererLog\Dao\OrderRefererDao;
use Biz\RefererLog\Dao\RefererLogDao;
use AppBundle\Common\ArrayToolkit;
use Biz\RefererLog\Service\RefererLogService;
use Topxia\Service\Common\ServiceKernel;

class RefererLogServiceImpl extends BaseService implements RefererLogService
{
    public function addRefererLog($refererlog)
    {
        if ($this->ignoreLog($refererlog)) {
            return false;
        }
        if (!ArrayToolkit::requireds($refererlog, array('targetId', 'targetType', 'refererUrl'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        if (!in_array($refererlog['targetType'], array('course', 'openCourse', 'classroom', 'vip'))) {
            throw $this->createServiceException("模块 {$refererlog['targetType']} 不允许添加RefererLog");
        }
        $user = $this->getCurrentUser();
        $refererlog = $this->prepareRefererUrl($refererlog);
        $refererlog['createdUserId'] = $user['id'];

        return $this->getRefererLogDao()->create($refererlog);
    }

    private function ignoreLog($refererlog)
    {
        //公开课管理页面不记录访问日志
        if ((bool) preg_match('/open\/course\/\d\/manage/', $refererlog['refererUrl'])) {
            return true;
        }
        //后台管理页面
        if ((bool) preg_match('/admin/', $refererlog['refererUrl'])) {
            return true;
        }

        return false;
    }

    public function getRefererLogById($id)
    {
        return $this->getRefererLogDao()->get($id);
    }

    public function waveRefererLog($id, $field, $diff)
    {
        return $this->getRefererLogDao()->wave(array($id), array($field => $diff));
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

        $totalCount = array_sum(ArrayToolkit::column($analysisSummaryList, 'count'));

        return array_map(function ($referelog) use ($totalCount) {
            $referelog['percent'] = empty($totalCount) ? '0%' : round($referelog['count'] / $totalCount * 100, 2).'%';
            $referelog['orderPercent'] = empty($referelog['count']) ? '0%' : round($referelog['orderCount'] / $referelog['count'] * 100, 2).'%';

            return $referelog;
        }, $analysisSummaryList);
    }

    public function countDistinctLogsByField($conditions, $field)
    {
        return $this->getRefererLogDao()->countDistinctLogsByField($conditions, $field);
    }

    protected function prepareConditions($conditions)
    {
        return $conditions;
    }

    public function searchRefererLogs($conditions, $orderBy, $start, $limit)
    {
        return $this->getRefererLogDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function countRefererLogs($conditions)
    {
        return $this->getRefererLogDao()->count($conditions);
    }

    public function findRefererLogsGroupByDate($conditions)
    {
        $timeRangeRefererLogCount = $this->countRefererLogs($conditions);

        $timeRangeRefererLogs = $this->searchRefererLogs($conditions, array('createdTime' => 'ASC'), 0, $timeRangeRefererLogCount);
        $timeRangeRefererLogs = array_map(function ($log) {
            $log['createdTime'] = date('Y-m-d', $log['createdTime']);

            return $log;
        }, $timeRangeRefererLogs);

        return ArrayToolkit::group($timeRangeRefererLogs, 'createdTime');
    }

    public function getOrderRefererByUv($uv)
    {
        return $this->getOrderRefererDao()->getByUv($uv);
    }

    public function getOrderRefererLikeByOrderId($orderId)
    {
        return $this->getOrderRefererDao()->getLikeByOrderId($orderId);
    }

    public function createOrderReferer($token)
    {
        return $this->getOrderRefererDao()->create($token);
    }

    public function updateOrderReferer($id, $fields)
    {
        return $this->getOrderRefererDao()->update($id, $fields);
    }

    /**
     * @return RefererLogDao
     */
    protected function getRefererLogDao()
    {
        return $this->createDao('RefererLog:RefererLogDao');
    }

    /**
     * @return OrderRefererDao
     */
    protected function getOrderRefererDao()
    {
        return $this->createDao('RefererLog:OrderRefererDao');
    }

    private function prepareAnalysisSummary($refererlogDatas)
    {
        if (empty($refererlogDatas)) {
            return array();
        }
        $lenght = 6;

        $analysisDatas = array_slice($refererlogDatas, 0, $lenght);
        $otherAnalysisDatas = count($refererlogDatas) >= $lenght ? array_slice($refererlogDatas, $lenght) : array();

        $totalCount = array_sum(ArrayToolkit::column($refererlogDatas, 'count'));
        $othertotalCount = array_sum(ArrayToolkit::column($otherAnalysisDatas, 'count'));
        $otherOrdertotalCount = array_sum(ArrayToolkit::column($otherAnalysisDatas, 'orderCount'));
        if (!empty($otherAnalysisDatas)) {
            array_push($analysisDatas, array('count' => $othertotalCount, 'orderCount' => $otherOrdertotalCount, 'refererName' => '其他'));
        }

        return array_map(function ($data) use ($totalCount) {
            $data['percent'] = empty($totalCount) ? '0%' : round($data['count'] / $totalCount * 100, 2).'%';
            $data['orderPercent'] = empty($data['count']) ? '0%' : round($data['orderCount'] / $data['count'] * 100, 2).'%';

            return $data;
        }, $analysisDatas);
    }

    private function prepareRefererUrl($refererlog)
    {
        $host = ServiceKernel::instance()->getEnvVariable('schemeAndHost');

        $refererMap = $this->getRefererMap();
        //微信访问url
        if (strpos($refererlog['userAgent'], 'MicroMessenger') !== false) {
            if ($refererlog['refererUrl'] == null || strpos($refererlog['refererUrl'], $host) !== false) {
                $refererlog['refererHost'] = 'mp.weixin.qq.com';
                $refererlog['refererUrl'] = 'mp.weixin.qq.com';
                $refererlog['refererName'] = $this->arrayFind($refererMap, $refererlog['refererHost']);

                return $refererlog;
            }
        }
        //直接访问url
        if ($refererlog['refererUrl'] == null) {
            $refererlog['refererUrl'] = $refererlog['uri'];
            $refererlog['refererHost'] = $host;
            $refererlog['refererName'] = '直接访问';

            return $refererlog;
        }

        $patten = '/^(https|http)?(:\/\/)?([^\/]+)/';
        preg_match($patten, $refererlog['refererUrl'], $matches);
        $outerVisit = true;
        array_walk($matches, function ($value) use ($host, &$outerVisit) {
            if ($value == $host) {
                $outerVisit = false;
            }
        });
        if ($outerVisit) {
            $refererlog['refererHost'] = $matches[0];
            $refererlog['refererName'] = $matches[0];
            $refererlog['refererName'] = $this->arrayFind($refererMap, $refererlog['refererHost']);
        } else {
            $refererlog['refererHost'] = $host;
            $refererlog['refererName'] = $host;
            $refererlog['refererName'] = $this->arrayFind($refererMap, $refererlog['refererUrl']);
        }

        return $refererlog;
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
        $host = ServiceKernel::instance()->getEnvVariable('host');

        return array(
            'open/course/explore' => '公开课列表',
            'open/course' => '公开课详情页',
            'my/courses/favorited' => '我的收藏',
            $host => '首页',

            'baidu.com' => '百度',
            'www.so.com' => '360搜索',
            'www.sogou.com' => '搜狗搜索',
            'mp.weixin.qq.com' => '微信',
            'weibo.com' => '微博',
        );
    }
}
