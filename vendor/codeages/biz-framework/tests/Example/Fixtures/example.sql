DROP TABLE IF EXISTS `example`;
CREATE TABLE `example` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `code` varchar(32) NOT NULL DEFAULT '',
  `counter1` int(10) unsigned NOT NULL DEFAULT 0,
  `counter2` int(10) unsigned NOT NULL DEFAULT 0,
  `ids1` varchar(32) NOT NULL DEFAULT '',
  `ids2` varchar(32) NOT NULL DEFAULT '',
  `null_value` VARCHAR(32) DEFAULT NULL,
  `content` text,
  `php_serialize_value` text,
  `json_serialize_value` text,
  `delimiter_serialize_value` text,
  `created_time` int(10) unsigned NOT NULL DEFAULT 0,
  `updated_time` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `example2`;
CREATE TABLE `example2` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `code` varchar(32) NOT NULL DEFAULT '',
  `counter1` int(10) unsigned NOT NULL DEFAULT 0,
  `counter2` int(10) unsigned NOT NULL DEFAULT 0,
  `ids1` varchar(32) NOT NULL DEFAULT '',
  `ids2` varchar(32) NOT NULL DEFAULT '',
  `null_value` VARCHAR(32) DEFAULT NULL,
  `content` text,
  `created_time` int(10) unsigned NOT NULL DEFAULT 0,
  `updated_time` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `example3`;
CREATE TABLE `example3` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `code` varchar(32) NOT NULL DEFAULT '',
  `counter1` int(10) unsigned NOT NULL DEFAULT 0,
  `counter2` int(10) unsigned NOT NULL DEFAULT 0,
  `ids1` varchar(32) NOT NULL DEFAULT '',
  `ids2` varchar(32) NOT NULL DEFAULT '',
  `null_value` VARCHAR(32) DEFAULT NULL,
  `content` text,
  `created_time` int(10) unsigned NOT NULL DEFAULT 0,
  `updated_time` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `example_uuid`;
CREATE TABLE `example_uuid` (
  `id` binary(16) NOT NULL,
  `name` varchar(32) NOT NULL DEFAULT '',
  `created_time` int(10) unsigned NOT NULL DEFAULT 0,
  `updated_time` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
