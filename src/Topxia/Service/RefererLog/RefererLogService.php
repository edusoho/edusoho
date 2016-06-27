<?php
namespace Topxia\Service\RefererLog;

interface RefererLogService
{
    public function addRefererLog($refererlog);

    public function getRefererLogById($id);
    /**
     * [searchAnalysisSummary 公开课数据统计 ->来源分析->汇总]
     * @param  [type] $conditions     [description]
     * @param  [type] $groupBy        [description]
     * @return [type] [description]
     */
    public function searchAnalysisSummary($conditions, $groupBy);
    /**
     * [searchAnalysisSummaryList 统计公开课的列表信息]
     * @param  [type] $conditions     [description]
     * @param  [type] $groupBy        [description]
     * @param  [type] $start          [description]
     * @param  [type] $limit          [description]
     * @return [type] [description]
     */
    public function searchAnalysisSummaryList($conditions, $groupBy, $start, $limit);

    public function searchAnalysisSummaryListCount($conditions);
    /**
     * [searchAnalysisDetail 统计单个功课开的的日志的统计信息]
     * @param  [type] $conditions     [description]
     * @param  [type] $groupBy        [description]
     * @return [type] [description]
     */
    public function searchAnalysisDetail($conditions, $groupBy);

    public function searchAnalysisDetailList($conditions, $groupBy, $start, $limit);

    public function searchAnalysisDetailListCount($conditions);

}
