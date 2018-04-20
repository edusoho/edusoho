<?php

use Phpmig\Migration\Migration;

class AddUuidIndex extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        if (!$this->isIndexExist('user', 'uuid')) {
            $connection->exec('
                CREATE UNIQUE INDEX `uuid` ON `user`(`uuid`);
            ');
        }
    }

    protected function isIndexExist($table, $indexName)
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $sql = "show index from `{$table}` where Key_name = '{$indexName}';";
        $result = $connection->fetchAssoc($sql);

        $sql = "show index from `{$table}` where Column_name = 'uuid';";
        $result = $connection->fetchAssoc($sql);

        var_dump('uuid: '.$result);

        $sql = 'select version();';
        $result = $connection->fetchAssoc($sql);

        var_dump('uuid: '.$result);

        return empty($result) ? false : true;
    }
}
