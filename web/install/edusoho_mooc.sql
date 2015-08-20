ALTER TABLE `course` ADD `startTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间';
ALTER TABLE `course` ADD `endTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间';
ALTER TABLE `course` ADD `rootId` int(10) unsigned DEFAULT '0' COMMENT '根课程ID';
ALTER TABLE `course` ADD `periods` int unsigned NOT NULL DEFAULT '1' COMMENT '周期课程的期数';
ALTER TABLE `course` ADD `certi` TINYINT unsigned NOT NULL DEFAULT  '0' COMMENT '是否发证';