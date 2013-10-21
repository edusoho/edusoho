<?php 
defined('WEKIT_VERSION') or exit(403);
/**
 * 全局配置
 */
return array(

/**=====配置开始于此=====**/

/*-----预设缓存键值-----*/

'precache' => array(
	'default/index/run' => array(
		array('hot_tags', array(0, 10)), 'medal_auto', 'medal_all'
	),
	'bbs/index/run' => array(
		array('hot_tags', array(0, 10)), 'medal_auto', 'medal_all'
	),
	'bbs/forum/run' => array(
		array('hot_tags', array(0, 10))
	),
	'bbs/cate/run' => array(
		array('hot_tags', array(0, 10))
	),
	'bbs/thread/run' => array(
		array('hot_tags', array(0, 10)), 'medal_auto', 'medal_all'
	),
	'bbs/read/run' => array('level', 'group_right', 'medal_all'),
),


/*-----预设钩子键值-----*/

'prehook' => array(
	'ALL' => array('s_head', 's_header_nav', 's_footer'),
	'LOGIN' => array('s_header_info_1', 's_header_info_2', 's_header_my'),
	'UNLOGIN' => array('s_header_info_3'),

	'default/index/run' => array('c_index_run', 'm_PwThreadList'),
	'bbs/index/run' => array('c_index_run', 'm_PwThreadList'),
	'bbs/cate/run' => array('c_cate_run', 'm_PwThreadList'),
	'bbs/thread/run' => array('c_thread_run', 'm_PwThreadList', 's_PwThreadType'),
	'bbs/read/run' => array('c_read_run', 'm_PwThreadDisplay', 's_PwThreadType', 's_PwUbbCode_convert', 's_PwThreadsHitsDao_add'),
	'bbs/post/doadd' => array('c_post_doadd', 'm_PwTopicPost', 's_PwThreadsDao_add', 's_PwThreadsIndexDao_add', 's_PwThreadsCateIndexDao_add', 's_PwThreadsContentDao_add', 's_PwForumStatisticsDao_update', 's_PwForumStatisticsDao_batchUpdate', 's_PwTagRecordDao_add', 's_PwTagRelationDao_add', 's_PwTagDao_update', 's_PwTagDao_add', 's_PwThreadsContentDao_update', 's_PwFreshDao_add', 's_PwUserDataDao_update', 's_PwUser_update', 's_PwAttachDao_update', 's_PwThreadAttachDao_update', 's_PwCreditOperationConfig'),
	'bbs/post/doreply' => array('c_post_doreply', 'm_PwReplyPost', 's_PwPostsDao_add', 's_PwForumStatisticsDao_update', 's_PwForumStatisticsDao_batchUpdate', 's_PwThreadsDao_update', 's_PwThreadsIndexDao_update', 's_PwThreadsCateIndexDao_update', 's_PwThreadsDigestIndexDao_update', 's_PwUserDataDao_update', 's_PwUser_update', 's_PwCreditOperationConfig'),
	'u/login/dorun' => array('c_login_dorun', 's_PwUserDataDao_update', 's_PwUser_update', 'm_PwLoginService'),
	'u/login/welcome' => array('s_PwUserDataDao_update', 's_PwUser_update', 'm_PwLoginService', 's_PwCronDao_update'),
	'u/register/dorun' => array('c_register', 'm_PwRegisterService'),
),


/*-----缓存用到的key-----*/

'cacheKeys' => array(
	'config' => array('config', array(), PwCache::USE_FILE, 'default', 0, array('cache.srv.PwCacheUpdateService', 'getConfigCacheValue')),
	'level' => array('level', array(), PwCache::USE_ALL, 'default', 0, array('usergroup.srv.PwUserGroupsService', 'getLevelCacheValue')),
	'group' => array('group_%s', array('gid'), PwCache::USE_ALL, 'default', 0, array('usergroup.srv.PwUserGroupsService', 'getGroupCacheValueByGid')),
	'group_right' => array('group_right', array(), PwCache::USE_ALL, 'default', 0, array('usergroup.srv.PwUserGroupsService', 'getGroupRightCacheValue')),
	'hot_tags' => array('hot_tags_%s_%s', array('cateid', 'num'), PwCache::USE_ALL, 'default', 3600, array('tag.srv.PwTagService', 'getHotTagsNoCache')),
	'medal_all' => array('medal_all', array(), PwCache::USE_ALL, 'default', 0, array('medal.srv.PwMedalService', 'getMedalAllCacheValue')),
	'medal_auto' => array('medal_auto', array(), PwCache::USE_ALL, 'default', 0, array('medal.srv.PwMedalService', 'getMedalAutoCacheValue')),
	'all_emotions' => array('all_emotions', array(), PwCache::USE_ALL, 'default', 0, array('emotion.srv.PwEmotionService', 'getAllEmotionNoCache')),
	'word' => array('word', array(), PwCache::USE_FILE, 'default', 0, array('SRV:word.srv.PwWordFilter', 'fetchAllWordNoCache')),
	'word_replace' => array('word_replace', array(), PwCache::USE_FILE, 'default', 0, array('SRV:word.srv.PwWordFilter', 'getReplaceWordNoCache')),
	'advertisement' => array('advertisement', array(), PwCache::USE_ALL, 'default', 0, array('SRV:advertisement.srv.PwAdService', 'getInstalledPosition')),
),

/**=====配置结束于此=====**/
);