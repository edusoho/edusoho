CREATE TABLE `client_device` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '反馈ID',
  `info` varchar(255) NOT NULL COMMENT '反馈描述',
  `type` varchar(255) NOT NULL COMMENT '反馈分类',
  `contact` varchar(255) NOT NULL COMMENT '联系方式',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;