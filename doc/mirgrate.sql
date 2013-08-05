DROP TABLE course_favorite;
DROP TABLE course_relatedgroup;
DROP TABLE group_collection;
DROP TABLE group_collection_item;
DROP TABLE group_ranking;
DROP TABLE group_share;
DROP TABLE likes;
DROP TABLE likes_stats;
DROP TABLE stack;
DROP TABLE stack_item;
DROP TABLE user_stat;
DROP TABLE advertisement;
DROP TABLE advertisement_publish;
DROP TABLE course_changelog;
DROP TABLE storage_simple;

ALTER TABLE  `category` CHANGE  `description`  `path` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

CREATE TABLE IF NOT EXISTS `cache` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `data` longblob,
  `serialized` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `expiredTime` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `expiredTime` (`expiredTime`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `setting` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `value` longblob NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;