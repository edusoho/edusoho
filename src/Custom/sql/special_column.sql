CREATE TABLE `special_column` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(64) NOT NULL,
  `name` varchar(255) NOT NULL,
  `subtitle` varchar(1024) ,
  `weight` int(11) NOT NULL DEFAULT '0',
  `classIndex` int(2) NOT NULL DEFAULT '1',
  `lowTagIds` 	 text,
  `middleTagIds` text,
  `highTagIds` 	 text,

  	
  `description` text,
  `smallAvatar` varchar(255) DEFAULT NULL,
  `mediumAvatar` varchar(255) DEFAULT NULL,
  `largeAvatar` varchar(255) DEFAULT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='专栏';