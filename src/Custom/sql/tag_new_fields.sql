ALTER TABLE `tag` ADD  `type` varchar(255) NOT NULL  AFTER `createdTime`;
ALTER TABLE `tag` ADD `sort` int(10) UNSIGNED DEFAULT '50' COMMENT '标签排序';