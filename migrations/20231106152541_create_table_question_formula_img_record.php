<?php

use Phpmig\Migration\Migration;

class CreateTableQuestionFormulaImgRecord extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE IF NOT EXISTS `question_formula_img_record` (
              `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
              `formula` text NOT NULL COMMENT '公式',
              `formula_hash` char(32) NOT NULL COMMENT '公式hash',
              `img` varchar(255) NOT NULL COMMENT '公式图片地址',
              `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `formula_hash` (`formula_hash`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('DROP TABLE IF EXISTS `question_formula_img_record`;');
    }
}
