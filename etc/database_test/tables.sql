CREATE TABLE `block` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL COMMENT '用户Id',
  `title` varchar(255) NOT NULL COMMENT '编辑时的题目',
  `content` text NOT NULL COMMENT '编辑区的内容',
  `code` varchar(255) NOT NULL,
  `createdTime` int(11) unsigned NOT NULL,
  `updateTime` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `block_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `blockId` int(11) NOT NULL COMMENT 'blockId',
  `content` text NOT NULL COMMENT 'content',
  `userId` int(11) NOT NULL COMMENT 'userId',
  `createdTime` int(11) unsigned NOT NULL COMMENT 'createdTime',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='历史表';

CREATE TABLE `cache` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `data` longblob,
  `serialized` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `expiredTime` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `expiredTime` (`expiredTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(64) NOT NULL,
  `name` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `weight` int(11) NOT NULL DEFAULT '0',
  `groupId` int(10) unsigned NOT NULL,
  `parentId` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uri` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `category_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(64) NOT NULL,
  `name` varchar(255) NOT NULL,
  `depth` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `comment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `objectType` varchar(32) NOT NULL,
  `objectId` int(10) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `objectType` (`objectType`,`objectId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `summary` text NOT NULL,
  `body` text NOT NULL,
  `picture` varchar(255) NOT NULL,
  `template` varchar(255) NOT NULL,
  `status` enum('published','unpublished','trash') NOT NULL,
  `categoryId` int(10) unsigned NOT NULL DEFAULT '0',
  `tagIds` tinytext NOT NULL,
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `featured` int(10) unsigned NOT NULL DEFAULT '0',
  `promoted` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '在列表中是否显示该条目。',
  `sticky` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否置顶',
  `userId` int(10) unsigned NOT NULL,
  `field1` text NOT NULL,
  `field2` text NOT NULL,
  `field3` text NOT NULL,
  `field4` text NOT NULL,
  `field5` text NOT NULL,
  `field6` text NOT NULL,
  `field7` text NOT NULL,
  `field8` text NOT NULL,
  `field9` text NOT NULL,
  `field10` text NOT NULL,
  `publishedTime` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `course` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(1024) NOT NULL,
  `subtitle` varchar(1024) NOT NULL COMMENT '副标题',
  `status` enum('draft','published','closed') NOT NULL DEFAULT 'draft' COMMENT '课程状态',
  `price` float(10,2) NOT NULL DEFAULT '0.00',
  `income` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '课程销售总收入',
  `lessonNum` int(10) unsigned NOT NULL DEFAULT '0',
  `rating` float unsigned NOT NULL DEFAULT '0' COMMENT '排行数值',
  `ratingNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '投票人数',
  `categoryId` int(10) unsigned NOT NULL DEFAULT '0',
  `tags` text NOT NULL,
  `smallPicture` varchar(255) NOT NULL,
  `middlePicture` varchar(255) NOT NULL,
  `largePicture` varchar(255) NOT NULL,
  `about` text NOT NULL,
  `teacherIds` text,
  `goals` text NOT NULL COMMENT '课程目标',
  `audiences` text NOT NULL COMMENT 'audiences',
  `recommended` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为推荐课程',
  `recommendedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间',
  `locationId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上课地区ID',
  `address` varchar(255) NOT NULL,
  `studentNum` int(10) unsigned NOT NULL DEFAULT '0',
  `hitNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '查看次数',
  `userId` int(10) unsigned NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `course_announcement` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userId` int(10) NOT NULL,
  `courseId` int(10) NOT NULL,
  `content` text NOT NULL,
  `createdTime` int(10) NOT NULL,
  `updatedTime` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `course_chapter` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `courseId` int(10) unsigned NOT NULL,
  `number` int(10) unsigned NOT NULL,
  `seq` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `course_favorite` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '收藏的id',
  `courseId` int(10) unsigned NOT NULL COMMENT '收藏课程的Id',
  `userId` int(10) unsigned NOT NULL COMMENT '收藏人的Id',
  `createdTime` int(10) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户的收藏数据表';

CREATE TABLE `course_lesson` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `courseId` int(10) unsigned NOT NULL,
  `chapterId` int(10) unsigned NOT NULL DEFAULT '0',
  `number` int(10) unsigned NOT NULL,
  `seq` int(10) unsigned NOT NULL,
  `free` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `status` enum('unpublished','published') NOT NULL DEFAULT 'published',
  `title` varchar(255) NOT NULL,
  `summary` text,
  `tags` text,
  `type` enum('text','video','audio') NOT NULL,
  `content` text,
  `media` text,
  `length` int(11) unsigned DEFAULT NULL,
  `materialNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传的资料数量',
  `quizNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '测验题目数量',
  `learnedNum` int(10) unsigned NOT NULL DEFAULT '0',
  `viewedNum` int(10) unsigned NOT NULL DEFAULT '0',
  `userId` int(10) unsigned NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `course_lesson_learn` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `courseId` int(10) unsigned NOT NULL,
  `lessonId` int(10) unsigned NOT NULL,
  `status` enum('learning','finished') NOT NULL,
  `startTime` int(10) unsigned NOT NULL DEFAULT '0',
  `finishedTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId_lessonId` (`userId`,`lessonId`),
  KEY `userId_courseId` (`userId`,`courseId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `course_material` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `courseId` int(10) unsigned NOT NULL,
  `lessonId` int(10) unsigned NOT NULL,
  `title` varchar(1024) NOT NULL,
  `description` text NOT NULL,
  `fileUri` varchar(255) NOT NULL,
  `fileMime` varchar(255) NOT NULL,
  `fileSize` int(10) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `course_member` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `courseId` int(10) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `learnedNum` int(10) unsigned NOT NULL DEFAULT '0',
  `noteNum` tinyint(3) unsigned NOT NULL COMMENT '笔记数目',
  `noteLastUpdateTime` int(10) unsigned NOT NULL COMMENT '最新的笔记更新时间',
  `isLearned` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `seq` int(10) unsigned NOT NULL DEFAULT '0',
  `isVisible` tinyint(2) NOT NULL DEFAULT '1' COMMENT '可见与否，默认为可见',
  `role` enum('student','teacher') NOT NULL DEFAULT 'student',
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `courseId` (`courseId`,`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `course_note` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userId` int(10) NOT NULL COMMENT '笔记作者ID',
  `courseId` int(10) NOT NULL COMMENT '课程ID',
  `lessonId` int(10) NOT NULL COMMENT '课时ID',
  `content` text NOT NULL COMMENT '笔记内容',
  `length` int(5) NOT NULL COMMENT '笔记内容的字数',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '笔记状态：0:私有, 1:公开',
  `createdTime` int(10) NOT NULL COMMENT '笔记创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '笔记更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `course_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sn` varchar(32) CHARACTER SET utf8 NOT NULL,
  `status` enum('created','paid') CHARACTER SET utf8 NOT NULL,
  `title` varchar(255) CHARACTER SET utf8 NOT NULL,
  `courseId` int(10) unsigned NOT NULL,
  `price` float unsigned NOT NULL,
  `isGift` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `giftTo` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `userId` int(10) unsigned NOT NULL,
  `payment` enum('none','alipay','tenpay') CHARACTER SET utf8 NOT NULL DEFAULT 'none',
  `bank` varchar(32) CHARACTER SET utf8 DEFAULT NULL COMMENT '银行编号',
  `paidTime` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sn` (`sn`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE `course_order_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `orderId` int(10) unsigned NOT NULL,
  `type` varchar(32) NOT NULL,
  `message` text,
  `data` text,
  `userId` int(10) unsigned NOT NULL,
  `ip` varchar(255) NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `orderId` (`orderId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `course_quiz` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `courseId` int(10) unsigned NOT NULL,
  `lessonId` int(10) unsigned NOT NULL,
  `itemIds` varchar(255) NOT NULL COMMENT '对应的题目Id',
  `answerIds` varchar(255) NOT NULL COMMENT '对应的答案Id',
  `userId` int(11) unsigned NOT NULL,
  `score` tinyint(4) NOT NULL DEFAULT '0' COMMENT '测验分数',
  `startTime` int(11) unsigned NOT NULL COMMENT '开始时间',
  `endTime` int(11) unsigned NOT NULL COMMENT '结束时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='测验的数据库表,自动生成';

CREATE TABLE `course_quiz_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `courseId` int(10) unsigned NOT NULL,
  `lessonId` int(10) unsigned NOT NULL,
  `description` text NOT NULL COMMENT '题目描述',
  `choices` text NOT NULL COMMENT '课时测验的可选结果',
  `answers` varchar(255) NOT NULL COMMENT '答案可以多个，用|竖线分割',
  `level` enum('low','normal','high') NOT NULL COMMENT '难度等级',
  `type` enum('single','multiple') NOT NULL COMMENT '问答题目类型',
  `userId` int(10) unsigned NOT NULL,
  `createdTime` int(11) unsigned NOT NULL COMMENT '课时测验创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='单个测验题目所对应的数据库表';

CREATE TABLE `course_quiz_item_answer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(11) unsigned NOT NULL,
  `quizId` int(10) unsigned NOT NULL,
  `itemId` int(10) unsigned NOT NULL,
  `answers` varchar(255) NOT NULL COMMENT '测验时用户提供的答案',
  `isCorrect` tinyint(1) NOT NULL COMMENT '测验正确与否结果',
  `createdTime` int(11) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用于记录用户答题的数据库表，对应与item的内容';

CREATE TABLE `course_review` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `courseId` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL COMMENT '评论title',
  `content` text NOT NULL COMMENT '评论内容',
  `rating` float unsigned NOT NULL DEFAULT '0',
  `createdTime` int(11) unsigned NOT NULL COMMENT '评价创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `course_thread` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `courseId` varchar(32) NOT NULL,
  `lessonId` int(11) NOT NULL,
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `type` enum('discussion','question') NOT NULL DEFAULT 'discussion',
  `isStick` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `isElite` tinyint(10) unsigned NOT NULL DEFAULT '0',
  `isClosed` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `postNum` int(10) unsigned NOT NULL DEFAULT '0',
  `hitNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击查看的次数',
  `followNum` int(11) NOT NULL,
  `latestPostUserId` int(10) unsigned NOT NULL DEFAULT '0',
  `latestPostTime` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `course_thread_post` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `courseId` varchar(32) NOT NULL,
  `lessonId` int(10) unsigned NOT NULL,
  `threadId` int(10) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `isElite` tinyint(4) NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `file` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `groupId` int(10) unsigned NOT NULL DEFAULT '0',
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `uri` varchar(255) NOT NULL,
  `mime` varchar(255) NOT NULL,
  `size` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `file_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `public` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `friend` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fromId` int(10) unsigned NOT NULL,
  `toId` int(10) unsigned NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `location` (
  `id` bigint(20) unsigned NOT NULL,
  `parentId` bigint(20) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `pinyin` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `module` varchar(32) NOT NULL,
  `action` varchar(32) NOT NULL,
  `message` text NOT NULL,
  `ip` varchar(255) NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  `level` char(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '私信Id',
  `fromId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发信人Id',
  `toId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收信人Id',
  `content` text NOT NULL,
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `message_conversation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '会话Id',
  `fromId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发信人Id',
  `toId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收信人Id',
  `messageNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '此对话的信息条数',
  `latestMessageUserId` int(10) unsigned DEFAULT NULL COMMENT '最后一条信息，用Json显示',
  `latestMessageTime` int(10) unsigned NOT NULL,
  `latestMessageContent` text NOT NULL,
  `unreadNum` int(10) unsigned NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `message_relation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `conversationId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '对话id',
  `messageId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '消息Id',
  `isRead` enum('0','1') NOT NULL DEFAULT '0' COMMENT '0表示未读',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `migration_versions` (
  `version` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `navigation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `name` varchar(255) NOT NULL COMMENT '文案',
  `url` varchar(300) NOT NULL COMMENT 'URL',
  `sequence` tinyint(4) unsigned NOT NULL COMMENT '显示顺序,数字替代',
  `createdTime` int(11) NOT NULL,
  `updateTime` int(11) NOT NULL,
  `type` varchar(30) NOT NULL COMMENT '类型',
  `isOpen` tinyint(2) NOT NULL DEFAULT '1' COMMENT '默认1，为开启',
  `isNewWin` tinyint(2) NOT NULL DEFAULT '1' COMMENT '默认为1,另开窗口',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='导航数据表';

CREATE TABLE `notification` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `type` varchar(64) NOT NULL DEFAULT 'default',
  `content` text NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  `isRead` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `session` (
  `session_id` varchar(255) NOT NULL,
  `session_value` text NOT NULL,
  `session_time` int(11) NOT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `setting` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `value` longblob,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(128) NOT NULL,
  `password` varchar(64) NOT NULL,
  `salt` varchar(32) NOT NULL,
  `uri` varchar(64) NOT NULL,
  `nickname` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `tags` varchar(255) NOT NULL,
  `type` varchar(32) NOT NULL COMMENT 'default默认为网站注册, weibo新浪微薄登录',
  `point` int(11) NOT NULL DEFAULT '0',
  `coin` int(11) NOT NULL DEFAULT '0',
  `smallAvatar` varchar(255) NOT NULL,
  `mediumAvatar` text NOT NULL,
  `largeAvatar` varchar(255) NOT NULL,
  `emailVerified` tinyint(1) NOT NULL DEFAULT '0',
  `roles` varchar(255) NOT NULL,
  `promoted` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为推荐',
  `promotedTime` int(10) unsigned NOT NULL COMMENT '推荐时间',
  `locked` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `loginTime` int(11) NOT NULL DEFAULT '0',
  `loginIp` varchar(64) NOT NULL,
  `newMessageNum` int(10) unsigned NOT NULL DEFAULT '0',
  `newNotificationNum` int(10) unsigned NOT NULL DEFAULT '0',
  `createdIp` varchar(64) NOT NULL,
  `createdTime` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `user_bind` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(64) NOT NULL,
  `fromId` varchar(32) NOT NULL,
  `toId` int(10) unsigned NOT NULL COMMENT '绑定的用户ID',
  `token` varchar(64) NOT NULL,
  `refreshToken` varchar(255) NOT NULL,
  `expiredTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'token过期时间',
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `type` (`type`,`fromId`),
  UNIQUE KEY `type_2` (`type`,`toId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `user_disk_file` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `filepath` varchar(2048) NOT NULL,
  `filename` varchar(1024) NOT NULL,
  `uri` varchar(1024) NOT NULL,
  `isDirectory` tinyint(1) NOT NULL DEFAULT '0',
  `size` int(11) NOT NULL DEFAULT '0',
  `mimeType` varchar(255) NOT NULL,
  `etag` varchar(128) NOT NULL,
  `type` enum('document','video','audio','image','other') NOT NULL DEFAULT 'other',
  `storage` enum('local','cloud') NOT NULL,
  `bucket` varchar(255) NOT NULL,
  `updatedTime` int(10) unsigned NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `user_fortune_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `number` int(10) NOT NULL,
  `action` varchar(20) NOT NULL,
  `note` varchar(20) NOT NULL,
  `createdTime` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_profile` (
  `id` int(10) unsigned NOT NULL,
  `truename` varchar(255) NOT NULL,
  `gender` enum('male','female','secret') NOT NULL DEFAULT 'secret',
  `birthday` date DEFAULT NULL,
  `city` varchar(64) NOT NULL,
  `mobile` varchar(32) NOT NULL,
  `qq` varchar(32) NOT NULL,
  `signature` text NOT NULL,
  `about` text NOT NULL,
  `company` varchar(255) NOT NULL,
  `job` varchar(255) NOT NULL,
  `weibo` varchar(255) NOT NULL,
  `weixin` varchar(255) NOT NULL,
  `site` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_token` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(64) NOT NULL,
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(255) NOT NULL,
  `data` text NOT NULL,
  `expiredTime` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`(6))
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;