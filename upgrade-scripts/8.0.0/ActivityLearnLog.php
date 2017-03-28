<?php 

class ActivityLearnLog extends AbstractMigrate
{
    public function update($page)
    {
    	if (!$this->isTableExist('activity_learn_log')) {
            $this->exec(
                "
                CREATE TABLE `activity_learn_log` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
                  `activityId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '教学活动id',
                  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
                  `event` varchar(255) NOT NULL DEFAULT '' COMMENT '',
                  `data` text COMMENT '',
                  `watchTime` int(10) unsigned NOT NULL DEFAULT '0',
                  `courseTaskId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '教学活动id',
                  `learnedTime` int(11) DEFAULT 0,
                  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                PRIMARY KEY (`id`)
              ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            "
            );
        }
    }
}