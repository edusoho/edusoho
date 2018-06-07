<?php

use Phpmig\Migration\Migration;

class ArticleContentBodyType extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("
            alter table content modify body longtext COMMENT '内容正文';
            alter table article modify body longtext COMMENT '内容正文';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
