<?php
namespace Topxia\Service\RefererLog;

interface RefererLogService
{
    public function addRefererLog($refererlog);

    public function getRefererLogById($id);
    /**
     * [searchAnalysisRefererLogSum 统计所有公开课的统计信息]
     * @param  [type] $conditions     [description]
     * @param  [type] $groupBy        [description]
     * @return [type] [description]
     */
    public function searchAnalysisRefererLogSum($conditions, $groupBy);
    /**
     * [searchAnalysisRefererLogs 统计公开课的列表信息]
     * @param  [type] $conditions     [description]
     * @param  [type] $groupBy        [description]
     * @param  [type] $start          [description]
     * @param  [type] $limit          [description]
     * @return [type] [description]
     */
    public function searchAnalysisRefererLogs($conditions, $groupBy, $start, $limit);
    /**
     * [searchAnalysisRefererLogsDetail 统计单个功课开的的日志的统计信息]
     * @param  [type] $conditions     [description]
     * @param  [type] $groupBy        [description]
     * @return [type] [description]
     */
    public function searchAnalysisRefererLogsDetail($conditions, $groupBy);

    public function searchRefererLogCount($conditions);
}
