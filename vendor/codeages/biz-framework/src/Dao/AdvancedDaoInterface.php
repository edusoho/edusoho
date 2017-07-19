<?php

namespace Codeages\Biz\Framework\Dao;

interface AdvancedDaoInterface extends GeneralDaoInterface
{
    public function deleteByConditions(array $conditions);

    public function batchCreate($rows);

    public function batchUpdate($identifies, $updateColumnsList, $identifyColumn = 'id');
}
