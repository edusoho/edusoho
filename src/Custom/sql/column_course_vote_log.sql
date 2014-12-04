

--
-- 表的结构 `column_course_vote_log`
--

CREATE TABLE IF NOT EXISTS `column_course_vote_log` (
`id` int(10) unsigned NOT NULL,
  `specialColumnId` int(10) NOT NULL,
  `columnCourseVoteId` int(10) NOT NULL,
  `voteCourseName` varchar(1024) NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `createdTime` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='专栏课程投票日志';


--
ALTER TABLE `column_course_vote_log`
 ADD PRIMARY KEY (`id`);



--
-- AUTO_INCREMENT for table `column_course_vote_log`
--
ALTER TABLE `column_course_vote_log`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

