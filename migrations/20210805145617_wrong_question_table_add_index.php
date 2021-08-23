<?php

use Phpmig\Migration\Migration;

class WrongQuestionTableAddIndex extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->createIndex('biz_wrong_question', 'collect_id', 'collect_id');
        $this->createIndex('biz_wrong_question', 'user_id', 'user_id');
        $this->createIndex('biz_wrong_question', 'item_id', 'item_id');
        $this->createIndex('biz_wrong_question', 'answer_scene_id', 'answer_scene_id');
        $this->createIndex('biz_wrong_question_collect', 'poolId_itemId', 'pool_id, item_id');
        $this->createIndex('biz_wrong_question_collect', 'item_id', 'item_id');
        $this->createIndex('biz_wrong_question_book_pool', 'user_id', 'user_id');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->dropIndex('biz_wrong_question', 'collect_id');
        $this->dropIndex('biz_wrong_question', 'user_id');
        $this->dropIndex('biz_wrong_question', 'item_id');
        $this->dropIndex('biz_wrong_question', 'answer_scene_id');
        $this->dropIndex('biz_wrong_question_collect', 'poolId_itemId');
        $this->dropIndex('biz_wrong_question_collect', 'item_id');
        $this->dropIndex('biz_wrong_question_book_pool', 'user_id');
    }

    protected function getConnection()
    {
        $biz = $this->getBiz();

        return $biz['db'];
    }

    protected function isIndexExist($table, $indexName)
    {
        $sql = "show index from `{$table}` where Key_name = '{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);

        return !empty($result);
    }

    protected function createIndex($table, $index, $column)
    {
        if (!$this->isIndexExist($table, $index)) {
            $this->getConnection()->exec("ALTER TABLE {$table} ADD INDEX {$index} ({$column});");
        }
    }

    protected function dropIndex($table, $index)
    {
        if ($this->isIndexExist($table, $index)) {
            $this->getConnection()->exec("DROP INDEX {$index} ON {$table};");
        }
    }

    protected function getBiz()
    {
        return $this->getContainer();
    }
}
