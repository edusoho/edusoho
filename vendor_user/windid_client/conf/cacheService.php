<?php 
defined('WEKIT_VERSION') or exit(403);

/**
 * 缓存服务配置
 */
return array(
	'PwUser' => 'user.cache.PwUserDbCache',
	'PwForum' => 'forum.cache.PwForumDbCache',
	'PwThread' => 'forum.cache.PwThreadDbCache',
	'forum.dao.PwPostsDao' => 'forum.cache.PwPostDbCache'
);