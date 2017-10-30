<?php

namespace Biz\Xapi\Service;

interface XapiService
{
    public function createStatement($statement);

    public function updateStatementsPushedByStatementIds($statementIds);

    public function updateStatementsPushingByStatementIds($statementIds);

    public function searchStatements($conditions, $orders, $start, $limit);
}
