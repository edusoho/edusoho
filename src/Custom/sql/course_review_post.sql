--
-- 表的结构 `course_review_post`
--

CREATE TABLE IF NOT EXISTS `course_review_post` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `courseId` int(10) NOT NULL,
  `reviewId` int(10) NOT NULL,
  `userId` int(10) NOT NULL,
  `content` varchar(1024) NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='评论回复';

