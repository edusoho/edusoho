--
-- 表的结构 `top_link`
--

CREATE TABLE IF NOT EXISTS `top_link` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `seq` int(10) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='顶部链接配置';
