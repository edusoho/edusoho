

--
-- 表的结构 `column_course_vote`
--

CREATE TABLE IF NOT EXISTS `column_course_vote` (
`id` int(10) unsigned NOT NULL,
  `specialColumnId` int(10) NOT NULL,
  `isShow` enum('active','none') DEFAULT NULL,
  `courseAName` varchar(1024) NOT NULL,
  `courseACount` int(10) NOT NULL DEFAULT 0,
  `courseBName` varchar(1024) NOT NULL,
  `courseBCount` int(10) NOT NULL DEFAULT 0,
  `courseVoteCount` int(10) NOT NULL DEFAULT 0,
  `voteStartTime` int(10) unsigned NOT NULL,
  `voteEndTime` int(10) unsigned NOT NULL,
  `createdTime` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='专栏课程投票';


ALTER TABLE `column_course_vote`
 ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `column_course_vote`
--
ALTER TABLE `column_course_vote`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

