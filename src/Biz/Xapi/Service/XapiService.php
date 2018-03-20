<?php

namespace Biz\Xapi\Service;

interface XapiService
{
    public function createStatement($statement);

    public function batchCreateStatements($statements);

    public function getStatement($id);

    public function deleteStatement($id);

    public function updateStatementsPushedByStatementIds($statementIds);

    public function updateStatementsPushingByStatementIds($statementIds);

    public function updateStatementsPushedAndDataByStatementData($pushStatementsData);

    public function updateStatementsConvertedAndDataByStatementData($pushStatementsData);

    public function updateStatusPushedAndPushedTimeByUuids($uuids, $pushTime);

    public function searchStatements($conditions, $orders, $start, $limit);

    public function countStatements($conditions);

    public function updateWatchLog($id, $watchLog);

    public function batchUpdateWatchLogPushed($watchLogIds);

    public function createWatchLog($watchLog);

    public function searchWatchLogs($conditions, $orderBys, $start, $limit);

    public function getWatchLog($id);

    public function findWatchLogsByIds($ids);

    public function watchTask($taskId, $watchTime);

    public function archiveStatement();

    public function getLatestWatchLogByUserIdAndActivityId($userId, $activityId, $isPush = 0);

    public function getXapiSdk();
}
