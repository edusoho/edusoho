<?php

use Phpmig\Migration\Migration;

class AddNeededUuidIndex extends Migration
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
        $filePath = $this->biz['kernel.root_dir'].'/data/specialEnv';
        var_dump($filePath);
        if (file_exists($filePath)) {
            return true;
        }

        $biz = $this->getContainer();
        $connection = $biz['db'];

        $sql = "show index from `{$table}` where Key_name = '{$indexName}';";
        $result = $connection->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
