CREATE TABLE `special_column` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `smallAvatar` varchar(255) DEFAULT NULL,
  `mediumAvatar` varchar(255) DEFAULT NULL,
  `largeAvatar` varchar(255) DEFAULT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='专栏';