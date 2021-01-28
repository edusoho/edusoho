<?php

namespace Biz\User\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

/**
 * Class UpdateStatusIndexJob
 */
class UpdateStatusIndexJob extends AbstractJob
{
    const TABLE = 'status';

    public function execute()
    {
        $this->createClassroomIdIndex();
    }

    protected function createClassroomIdIndex()
    {
        $this->createIndex(self::TABLE, 'classroomId', 'classroomId');
    }

    protected function getConnection()
    {
        $biz = $this->getBiz();

        return $biz['db'];
    }

    protected function isIndexExist($table, $filedName, $indexName)
    {
        $sql = "show index from `{$table}` where column_name = '{$filedName}' and Key_name = '{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function createIndex($table, $index, $column)
    {
        if (!$this->isIndexExist($table, $column, $index)) {
            $this->getConnection()->exec("ALTER TABLE {$table} ADD INDEX {$index} ({$column})");
        }
    }

    protected function getBiz()
    {
        return $this->biz;
    }
}
