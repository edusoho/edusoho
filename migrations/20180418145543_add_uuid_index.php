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

        if (!$this->isUniqueIndexExist('user', 'uuid')) {
            $connection->exec('
                CREATE UNIQUE INDEX `uuid` ON `user`(`uuid`);
            ');
        }
    }

    protected function isUniqueIndexExist($table, $indexName)
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $params = $connection->getParams();
        $dbName = $params['dbname'];

        $sql = "SELECT * FROM information_schema.statistics WHERE table_schema = '{$dbName}' and table_name = '{$table}' AND index_name = '{indexName}';";
        $result = $connection->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
