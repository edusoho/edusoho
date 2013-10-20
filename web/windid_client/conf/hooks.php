<?php
defined('WEKIT_VERSION') or exit(403);
/**
 * 系统hook配置文件
 */
return array(
	'c_post_run' => array(
		'description' => '发表帖子展示页',
		'param' => array(),
		'interface' => '',
		'list' => array(
			'poll' => array(
				'class' => 'SRV:forum.srv.post.injector.PwPostDoPollInjector', 
				'method' => 'run', 
				'expression' => 'special.get==poll',
				'description' => '投票帖展示'
			)
		)
	),
	'c_post_doadd' => array(
		'description' => '发表帖子提交页',
		'param' => array(),
		'interface' => '',
		'list' => array(
			'poll' => array(
				'class' => 'SRV:forum.srv.post.injector.PwPostDoPollInjector', 
				'method' => 'doadd',
				'expression' => 'special.post==poll',
				'description' => '发投票帖'
			), 
			'att' => array(
				'class' => 'SRV:forum.srv.post.injector.PwPostDoAttInjector', 
				'method' => 'run', 
				'expression' => 'flashatt.post!=0',
				'description' => '发帖传附件'
			), 
			'tag' => array(
				'class' => 'SRV:forum.srv.post.injector.PwPostDoTagInjector', 
				'method' => 'doadd',
				'description' => '帖子发布 - 话题相关'
			),
			'word' => array(
				'class' => 'SRV:forum.srv.post.injector.PwPostDoWordInjector', 
				'method' => 'doadd',
				'description' => '帖子发布 - 敏感词'
			)
		)
	), 
	'c_post_doreply' => array(
		'description' => '发表回复提交页',
		'param' => array(),
		'interface' => '',
		'list' => array(
			'att' => array(
				'class' => 'SRV:forum.srv.post.injector.PwPostDoAttInjector', 
				'method' => 'run', 
				'expression' => 'flashatt.post!=0',
				'description' => '回复发布 - 附件'
			), 
			'dolike_fast_reply' => array(
				'class' => 'SRV:like.srv.fresh.injector.PwLikeDoFreshInjector', 
				'method' => 'run', 
				'expression' => 'isfresh.post==1',
				'description' => '回复发布 - 喜欢'
			), 
			'dolike_reply_lastpid' => array(
				'class' => 'SRV:like.srv.reply.injector.PwLikeDoReplyInjector', 
				'method' => 'run', 
				'expression' => 'from_type.post==like',
				'description' => '回复发布 - 最后喜欢的回复'
			),
			'word' => array(
				'class' => 'SRV:forum.srv.post.injector.PwPostDoWordInjector', 
				'method' => 'doadd',
				'description' => '帖子发布 - 敏感词'
			)
		)
	), 
	'c_post_modify' => array(
		'description' => '帖子编辑页面',
		'param' => array(),
		'interface' => '',
		'list' => array(
			'poll' => array(
				'class' => 'SRV:forum.srv.post.injector.PwPostDoPollInjector', 
				'method' => 'modify', 
				'expression' => 'service:special==poll',
				'description' => '帖子编辑 - 投票帖'
			),
		)
	), 
	'c_post_domodify' => array(
		'description' => '帖子编辑提交页面',
		'param' => array(),
		'interface' => '',
		'list' => array(
			'poll' => array(
				'class' => 'SRV:forum.srv.post.injector.PwPostDoPollInjector', 
				'method' => 'domodify', 
				'expression' => 'service:special==poll',
				'description' => '帖子编辑提交 - 投票帖'
			), 
			'att' => array(
				'class' => 'SRV:forum.srv.post.injector.PwPostDoAttInjector', 
				'method' => 'domodify',
				'description' => '帖子编辑 - 附件'
			),
			'tag' => array(
				'class' => 'SRV:forum.srv.post.injector.PwPostDoTagInjector', 
				'method' => 'domodify',
				'description' => '帖子编辑 - 话题'
			),
			'word' => array(
				'class' => 'SRV:forum.srv.post.injector.PwPostDoWordInjector', 
				'method' => 'doadd',
				'description' => '帖子发布 - 敏感词'
			)
		)
	),
	'c_index_run' => array(
		'description' => '新帖列表页',
		'param' => array(),
		'interface' => '',
		'list' => array(
		),
	),
	'c_cate_run' => array(
		'description' => '分类帖子列表页',
		'param' => array(),
		'interface' => '',
		'list' => array(
		),
	),
	'c_thread_run' => array(
		'description' => '版块帖子列表页',
		'param' => array(),
		'interface' => '',
		'list' => array(
		),
	),
	'c_read_run' => array(
		'description' => '帖子阅读页',
		'param' => array(),
		'interface' => '',
		'list' => array(
			'poll' => array(
				'class' => 'SRV:forum.srv.threadDisplay.injector.PwThreadDisplayDoPollInjector', 
				'method' => 'run', 
				'expression' => 'service:thread.info.special==poll',
				'description' => '帖子阅读页 - 投票帖'
			),
			'like' => array(
				'class' => 'SRV:like.srv.threadDisplay.injector.PwThreadDisplayDoLikeInjector', 
				'method' => 'run',
				'expression' => 'service:thread.info.like_count!=0',
				'description' => '帖子阅读页 - 喜欢'
			),
			'medal' => array(
				'class' => 'SRV:medal.srv.threadDisplay.injector.PwThreadDisplayDoMedalInjector', 
				'method' => 'run',
				'expression' => 'config:site.medal.isopen==1',
				'description' => '帖子阅读页 - 勋章'
			),
			'word' => array(
				'class' => 'SRV:forum.srv.threadDisplay.injector.PwThreadDisplayDoWordInjector', 
				'expression' => 'service:thread.info.word_version==0',
				'description' => '帖子阅读页 - 替换敏感词'
			),
		)
	),
	'c_register' => array(
		'description' => '注册页面',
		'param' => array(),
		'interface' => 'LIB:engine.hook.PwBaseHookInjector',
		'list' => array(
			'invite' => array(
				'class' => 'SRV:user.srv.register.injector.PwRegisterDoInviteInjector',
				'method' => 'run',
				'expression' => 'service:isOpenInvite==1'
			),
			'inviteFriend' => array(
				'class' => 'SRV:user.srv.register.injector.PwRegisterDoInviteFriendInjector',
				'method' => 'run',
			),
			'verifyMobile' => array(
				'class' => 'SRV:user.srv.register.injector.PwRegisterDoVerifyMobileInjector',
				'method' => 'run',
			),
		)
	),
	'c_fresh_post' => array(
		'description' => '在新鲜事页面发布帖子',
		'param' => array(),
		'interface' => '',
		'list' => array(
			'att' => array(
				'class' => 'SRV:forum.srv.post.injector.PwPostDoAttInjector', 
				'method' => 'run', 
				'expression' => 'flashatt.post!=0'
			)
		)
	),
	'c_profile_extends_run' => array (
		'description' => '用户菜单功能扩展-展示',
		'param' => array(),
		'list' => array(
		),
	),
	'c_profile_extends_dorun' => array (
		'description' => '用户菜单功能扩展-执行',
		'param' => array(),
		'list' => array(
		),
	),
	'c_login_dorun' => array(
		'description' => '用户登录，表现层',
		'param' => array(),
		'interface' => '',
		'list' => array(
			'inviteFriend' => array(
				'class' => 'SRV:user.srv.login.injector.PwLoginDoInviteFriendInjector',
				'method' => 'run'
			),
		)
	),
	'm_PwRegisterService' => array(
		'description' => '注册Service钩子',
		'param' => array(),
		'interface' => 'SRV:user.srv.register.do.PwRegisterDoBase',
		'list' => array(
			'bbsinfo' => array(
				'class' => 'SRV:user.srv.register.do.PwRegisterDoUpdateBbsInfo',
				'description' => '注册后期：更新站点信息'
			),
		)
	),
	'm_PwTopicPost' => array(
		'description' => '发表帖子',
		'param' => array(),
		'interface' => 'SRV:forum.srv.post.do.PwPostDoBase',
		'list' => array(
			'fresh' => array(
				'class' => 'HOOK:PwPost.do.PwPostDoFresh',
				'description' => '新鲜事'
			),
			'task' => array(
				'class' => 'SRV:task.srv.condition.PwTaskBbsThreadDo',
				'expression' => 'config:site.task.isOpen==1',
				'description' => '发帖做任务'
			),
			'behavior' => array(
				'class' => 'SRV:misc.behavior.do.PwMiscThreadDo',
				'loadway' => 'load',
				'description' => '记录发帖行为'
			),
			'medal' => array(
				'class' => 'SRV:medal.srv.condition.do.PwMedalThreadDo',
				'description' => '发帖做勋章'
			),
			'remind' => array(
				'class' => 'SRV:forum.srv.post.do.PwPostDoRemind',
			),
			'word' => array(
				'class' => 'SRV:forum.srv.post.do.PwReplyDoWord',
				'description' => '回复-敏感词'
			),
		)
	),
	'm_PwReplyPost' => array(
		'description' => '发表回复',
		'param' => array(),
		'interface' => 'SRV:forum.srv.post.do.PwPostDoBase',
		'list' => array(
			'task' => array(
				'expression' => 'config:site.task.isOpen==1',
				'class' => 'SRV:task.srv.condition.PwTaskBbsPostDo',
				'description' => '发回复做任务'
			),
			'behavior' => array(
				'class' => 'SRV:misc.behavior.do.PwMiscPostDo',
				'loadway' => 'load',
				'description' => '记录发回复行为'
			),
			'medal' => array(
				'class' => 'SRV:medal.srv.condition.do.PwMedalPostDo',
				'description' => '发回复做勋章任务'
			),
			'remind' => array(
				'class' => 'SRV:forum.srv.post.do.PwReplyDoRemind',
				'description' => '回复-话题'
			),
			'notice' => array(
				'class' => 'SRV:forum.srv.post.do.PwReplyDoNotice',
				'description' => '回复-通知'
			),
			'word' => array(
				'class' => 'SRV:forum.srv.post.do.PwReplyDoWord',
				'description' => '回复-敏感词'
			),
		)
	),
	'm_PwThreadList' => array(
		'description' => '帖子列表页',
		'param' => array(),
		'interface' => 'SRV:forum.srv.threadList.do.PwThreadListDoBase',
		'list' => array(
			'hits' => array(
				'class' => 'SRV:forum.srv.threadList.do.PwThreadListDoHits',
				'description' => '点击率实时更新显示',
				'expression' => 'config:bbs.read.hit_update==1',
			)
		),
	),
	'm_PwThreadDisplay' => array(
		'description' => '帖子内容展示',
		'param' => array(),
		'interface' => 'SRV:forum.srv.threadDisplay.do.PwThreadDisplayDoBase',
		'list' => array(
			'hits' => array(
				'class' => 'SRV:forum.srv.threadDisplay.do.PwThreadDisplayDoHits',
				'description' => '点击率实时更新显示',
				'expression' => 'config:bbs.read.hit_update==1',
			)
		)
	),
	/*获取任务奖励钩子*/
	'm_task_gainreward' => array(
		'description' => '领取任务',
		'param' => array(),
		'interface' => 'SRV:task.srv.reward.PwTaskRewardDoBase',
		'list' => array(
			'group' => array(
				'class' => 'SRV:task.srv.reward.PwTaskGroupRewardDo',
				'expression' => 'service:type==group',
			),
			'credit' => array(
				'class' => 'SRV:task.srv.reward.PwTaskCreditRewardDo',
				'expression' => 'service:type==credit',
			),
		)
	),
	'm_PwMessageService' => array(
		'description' => '消息服务',
		'param' => array(),
		'interface' => 'SRV:message.srv.do.PwMessageDoBase',
		'list' => array(
			'task' => array(
				'expression' => 'config:site.task.isOpen==1',
				'class' => 'SRV:task.srv.condition.PwTaskMemberMsgDo',
				'loadway' => 'load'
			)
		)
	),
	'm_PwLoginService' => array(
		'description' => '用户登录之后的操作',
		'param' => array('@param PwUserBo $userBo 登录用户的对象', '@param string $ip 登录的IP'),
		'interface' => 'SRV:user.srv.login.PwUserLoginDoBase',
		'list' => array(
			'autotask' => array(
				'expression' => 'config:site.task.isOpen==1',
				'class' => 'SRV:task.srv.condition.PwAutoTaskLoginDo',
				'loadway' => 'load'
			),
			'userbelong' => array(
				'class' => 'HOOK:PwUser.PwUserLoginDoBelong',
				'loadway' => 'load'
			),
			'behavior' => array(
				'class' => 'SRV:misc.behavior.do.PwMiscUserDo',
				'loadway' => 'load'
			),
			'medal' => array(
				'class' => 'SRV:medal.srv.condition.do.PwMedalUserDo',
				'loadway' => 'load'
			),
			'updateOnline' => array(
				'class' => 'SRV:online.srv.do.PwLoginDoUpdateOnline',
				'loadway' => 'load'
			),
			'autounbancheck' => array(
				'class' => 'SRV:user.srv.login.do.PwLoginDoUnbanCheck',
				'loadway' => 'load'
			),
			/*
			'recommendUser' => array(
				'class' => 'SRV:attention.srv.recommend.PwRecommendUserDo',
				'loadway' => 'load'
			),*/
		)
	),
	'm_PwFreshReplyByWeibo' => array(
		'description' => '微博',
		'param' => array(),
		'interface' => 'SRV:attention.srv.reply.weibo.PwWeiboDoBase',
		'list' => array(
			'word' => array(
				'class' => 'SRV:attention.srv.reply.weibo.PwWeiboDoWord',
				'description' => '微博-敏感词'
			),
		)
	),
	's_PwThreadsDao_add' => array(
		'description' => '增加一条帖子记录时，调用',
		'param' => array('@param int $id 新增的帖子tid', '@param array $fields 帖子字段', '@return void'),
		'interface' => '',
		'list' => array(
			'threadsIndex' => array(
				'class' => 'SRV:forum.dao.PwThreadsIndexDao',
				'method' => 'addThread',
				'loadway' => 'loadDao'
			),
			'threadsCateIndex' => array(
				'class' => 'SRV:forum.dao.PwThreadsCateIndexDao',
				'method' => 'addThread',
				'loadway' => 'loadDao'
			),
			'threadsDigestIndex' => array(
				'class' => 'SRV:forum.dao.PwThreadsDigestIndexDao',
				'method' => 'addThread',
				'loadway' => 'loadDao'
			),
		)
	),
	's_PwThreadsDao_update' => array(
		'description' => '更新一条帖子记录时，调用',
		'param' => array('@param int $id 帖子tid', '@param array $fields 更新的帖子字段数据', '@param array $increaseFields 递增的帖子字段数据', '@return void'),
		'interface' => '',
		'list' => array(
			'threadsIndex' => array(
				'class' => 'SRV:forum.dao.PwThreadsIndexDao',
				'method' => 'updateThread',
				'loadway' => 'loadDao'
			),
			'threadsCateIndex' => array(
				'class' => 'SRV:forum.dao.PwThreadsCateIndexDao',
				'method' => 'updateThread',
				'loadway' => 'loadDao'
			),
			'threadsDigestIndex' => array(
				'class' => 'SRV:forum.dao.PwThreadsDigestIndexDao',
				'method' => 'updateThread',
				'loadway' => 'loadDao'
			),
		)
	),
	's_PwThreadsDao_batchUpdate' => array(
		'description' => '批量更新多条帖子记录时，调用',
		'param' => array('@param array $ids 帖子tid序列', '@param array $fields 更新的帖子字段数据', '@param array $increaseFields 递增的帖子字段数据', '@return void'),
		'interface' => '',
		'list' => array(
			'threadsIndex' => array(
				'class' => 'SRV:forum.dao.PwThreadsIndexDao',
				'method' => 'batchUpdateThread',
				'loadway' => 'loadDao'
			),
			'threadsCateIndex' => array(
				'class' => 'SRV:forum.dao.PwThreadsCateIndexDao',
				'method' => 'batchUpdateThread',
				'loadway' => 'loadDao'
			),
			'threadsDigestIndex' => array(
				'class' => 'SRV:forum.dao.PwThreadsDigestIndexDao',
				'method' => 'batchUpdateThread',
				'loadway' => 'loadDao'
			),
		)
	),
	's_PwThreadsDao_revertTopic' => array(
		'description' => '还原帖子时，调用',
		'param' => array('@param array $tids 帖子tid序列', '@return void'),
		'interface' => '',
		'list' => array(
			'threadsIndex' => array(
				'class' => 'SRV:forum.dao.PwThreadsIndexDao',
				'method' => 'revertTopic',
				'loadway' => 'loadDao'
			),
			'threadsCateIndex' => array(
				'class' => 'SRV:forum.dao.PwThreadsCateIndexDao',
				'method' => 'revertTopic',
				'loadway' => 'loadDao'
			),
			'threadsDigestIndex' => array(
				'class' => 'SRV:forum.dao.PwThreadsDigestIndexDao',
				'method' => 'revertTopic',
				'loadway' => 'loadDao'
			),
		)
	),
	's_PwThreadsDao_delete' => array(
		'description' => '删除一个帖子时，调用',
		'param' => array('@param int $id 帖子tid', '@return void'),
		'interface' => '',
		'list' => array(
			'threadsIndex' => array(
				'class' => 'SRV:forum.dao.PwThreadsIndexDao',
				'method' => 'deleteThread',
				'loadway' => 'loadDao'
			),
			'threadsCateIndex' => array(
				'class' => 'SRV:forum.dao.PwThreadsCateIndexDao',
				'method' => 'deleteThread',
				'loadway' => 'loadDao'
			),
			'threadsDigestIndex' => array(
				'class' => 'SRV:forum.dao.PwThreadsDigestIndexDao',
				'method' => 'deleteThread',
				'loadway' => 'loadDao'
			),
		)
	),
	's_PwThreadsDao_batchDelete' => array(
		'description' => '批量删除多个帖子时，调用',
		'param' => array('@param array $ids 帖子tid序列', '@return void'),
		'interface' => '',
		'list' => array(
			'threadsIndex' => array(
				'class' => 'SRV:forum.dao.PwThreadsIndexDao',
				'method' => 'batchDeleteThread',
				'loadway' => 'loadDao'
			),
			'threadsCateIndex' => array(
				'class' => 'SRV:forum.dao.PwThreadsCateIndexDao',
				'method' => 'batchDeleteThread',
				'loadway' => 'loadDao'
			),
			'threadsDigestIndex' => array(
				'class' => 'SRV:forum.dao.PwThreadsDigestIndexDao',
				'method' => 'batchDeleteThread',
				'loadway' => 'loadDao'
			),
		)
	),
	's_addFollow' => array(
		'description' => '当发生关注操作时，调用',
		'param' => array('@param int $uid 用户', '@param int $touid 被关注用户', '@return void'),
		'interface' => '',
		'list' => array(
			'medal' => array(
				'class' => 'SRV:medal.srv.condition.do.PwMedalFansDo',
				'method' => 'addFollow',
				'loadway' => 'load'
			),
			'task' => array(
				'expression' => 'config:site.task.isOpen==1',
				'class' => 'SRV:task.srv.condition.PwTaskMemberFansDo',
				'method' => 'addFollow',
				'loadway' => 'load',
			),
			'message' => array(
				'class' => 'SRV:message.srv.do.PwNoticeFansDo',
				'method' => 'addFollow',
				'loadway' => 'load',
			),
		)
	),
	's_deleteFollow' => array(
		'description' => '当发生取消关注操作时，调用',
		'param' => array('@param int $uid 用户', '@param int $touid 被关注用户', '@return void'),
		'interface' => '',
		'list' => array(
			'medal' => array(
				'class' => 'SRV:medal.srv.condition.do.PwMedalFansDo',
				'method' => 'delFollow',
				'loadway' => 'load'
			),
			/*
			'recommend' => array(
				'class' => 'SRV:attention.srv.recommend.PwRecommendAttentionDo',
				'method' => 'delFollow',
				'loadway' => 'load'
			),*/
		)
	),
	
	's_PwTaskDao_update' => array(
		'description' => '更新一条任务记录时，调用',
		'param' => array('@param int $id 帖子tid', '@param array $fields 更新的任务字段数据', '@param array $increaseFields 递增的任务字段数据', '@return void'),
		'interface' => '',
		'list' => array(
			'TaskUser' => array(
				'class' => 'SRV:task.dao.PwTaskUserDao',
				'method' => 'updateIsPeriod',
				'loadway' => 'loadDao'
			)
		)
	),
	's_profile_editUser' => array(
		'description' => '更新用户资料时，调用',
		'param' => array('@param PwUserInfoDm $dm', '@return void'),
		'interface' => '',
		'list' => array(
			'task' => array(
				'expression' => 'config:site.task.isOpen==1',
				'class' => 'SRV:task.srv.condition.PwTaskProfileConditionDo',
				'loadway' => 'load',
				'method' => 'editUser',
			),
		)
	),
	's_update_avatar' => array(
		'description' => '更新用户头像时，调用',
		'param' => array('@param int $uid 用户id', '@return void'),
		'interface' => '',
		'list' => array(
			'task' => array(
				'expression' => 'config:site.task.isOpen==1',
				'class' => 'SRV:task.srv.condition.PwTaskMemberAvatarDo',
				'loadway' => 'load',
				'method' => 'uploadAvatar',
			),
		)
	),
	's_PwUser_delete' => array(
		'description' => '删除用户时，调用',
		'param' => array('@param int $uid 用户id', '@return void'),
		'interface' => '',
		'list' => array(
			'ban' => array(
				'class' => 'SRC:hooks.PwUser.PwUserDoBan',
				'method' => 'deleteBan',
				'loadway' => 'load'
			),
			'belong' => array(
				'class' => 'SRC:hooks.PwUser.PwUserDoBelong',
				'method' => 'deleteUser',
				'loadway' => 'load'
			),
			'registerCheck' => array(
				'class' => 'SRC:hooks.PwUser.PwUserDoRegisterCheck',
				'method' => 'deleteUser',
				'loadway' => 'load',
			),
			'activeCode' => array(
				'class' => 'SRV:user.PwUserActiveCode',
				'method' => 'deleteInfoByUid',
				'loadway' => 'load',
			),
			'task' => array(
				'class' => 'SRV:task.PwTaskUser',
				'method' => 'deleteByUid',
				'loadway' => 'load',
			),
			'usertag' => array(
				'class' => 'SRV:usertag.PwUserTagRelation',
				'method' => 'deleteRelationByUid',
				'loadway' => 'load',
			),
			'mobile' => array(
				'class' => 'SRV:user.PwUserMobile',
				'method' => 'deleteByUid',
				'loadway' => 'load',
			),
		)
	),
	's_PwUser_batchDelete' => array(
		'description' => '批量删除用户时，调用',
		'param' => array('@param array $uids 用户id序列', '@return void'),
		'interface' => '',
		'list' => array(
			'ban' => array(
				'class' => 'SRC:hooks.PwUser.PwUserDoBan',
				'method' => 'batchDeleteBan',
				'loadway' => 'load'
			),
			'belong' => array(
				'class' => 'SRC:hooks.PwUser.PwUserDoBelong',
				'method' => 'batchDeleteUser',
				'loadway' => 'load'
			),
			'registerCheck' => array(
				'class' => 'SRC:hooks.PwUser.PwUserDoRegisterCheck',
				'method' => 'batchDeleteUser',
				'loadway' => 'load',
			),
			'task' => array(
				'class' => 'SRV:task.PwTaskUser',
				'method' => 'batchDeleteByUid',
				'loadway' => 'load',
			),
			'usertag' => array(
				'class' => 'SRV:usertag.PwUserTagRelation',
				'method' => 'batchDeleteRelationByUids',
				'loadway' => 'load',
			),
		)
	),
	's_PwUser_add' => array(
		'description' => '添加用户时，调用',
		'param' => array('@param PwUserInfoDm $dm', '@return void'),
		'interface' => '',
		'list' => array(
			'belong' => array(
				'class' => 'SRC:hooks.PwUser.PwUserDoBelong',
				'method' => 'editUser',
				'loadway' => 'load'
			),
		)
	),
	's_PwUser_update' => array(
		'description' => '更新用户信息时，调用',
		'param' => array('@param PwUserInfoDm $dm', '@return void'),
		'interface' => '',
		'list' => array(
			'belong' => array(
				'class' => 'SRC:hooks.PwUser.PwUserDoBelong',
				'method' => 'editUser',
				'loadway' => 'load'
			),
		)
	),
	's_PwUserDataDao_update' => array(
		'description' => '用户数据更新时，调用',
		'param' => array('@param int $id 用户id', '@param array $fields 更新的用户字段数据', '@param array $increaseFields 递增的用户字段数据', '@return void'),
		'interface' => '',
		'list' => array(
			'level' => array(
				'class' => 'SRV:usergroup.srv.PwUserGroupsService',
				'method' => 'updateLevel',
				'loadway' => 'load'
			),
			'autoBan' => array(
				'class' => 'SRV:user.srv.PwUserBanService',
				'method' => 'autoBan',
				'loadway' => 'load',
				'expression' => 'config:site.autoForbidden.open==1',
			),
		)
	),
	's_PwUserGroups_update' => array(
		'description' => '用户组资料更新时，调用',
		'param' => array('@param int $gid 用户组id', '@return void'),
		'interface' => '',
		'list' => array(
			'usergroup' => array(
				'class' => 'SRV:usergroup.srv.PwUserGroupsService',
				'method' => 'updateGroupCacheByHook',
				'loadway' => 'load'
			),
		)
	),
	's_PwUserGroupsDao_delete' => array(
		'description' => '删除用户组时，调用',
		'param' => array('@param int $gid 用户组id', '@return void'),
		'interface' => '',
		'list' => array(
			'usergroup' => array(
				'class' => 'SRV:usergroup.srv.PwUserGroupsService',
				'method' => 'deleteGroupCacheByHook',
				'loadway' => 'load'
			),
		)
	),
	's_PwUserGroupPermission_update' => array(
		'description' => '用户组权限变更时，调用',
		'param' => array('@param PwUserPermissionDm $dm', '@return void'),
		'interface' => '',
		'list' => array(
			'usergroup_permission' => array(
				'class' => 'SRV:usergroup.srv.PwUserGroupsService',
				'method' => 'updatePermissionCacheByHook',
				'loadway' => 'load'
			),
		)
	),
	's_PwLikeService_delLike' => array( 
		'description' => '删除喜欢',
		'list' => array(
			'behavior' => array(
				'class' => 'SRV:misc.behavior.do.PwMiscLikeDo',
				'method' => 'delLike',
				'loadway' => 'load'
			),
			'medal' => array(
				'class' => 'SRV:medal.srv.condition.do.PwMedalLikeDo',
				'method' => 'delLike',
				'loadway' => 'load'
			),
		)
	),
	's_PwLikeService_addLike' => array( 
		'description' => '添加喜欢',
		'list' => array(
			'task' => array(
				'expression' => 'config:site.task.isOpen==1',
				'class' => 'SRV:task.srv.condition.PwTaskBbsLikeDo',
				'method' => 'addLike',
				'loadway' => 'load'
			),
			'behavior' => array(
				'class' => 'SRV:misc.behavior.do.PwMiscLikeDo',
				'method' => 'addLike',
				'loadway' => 'load'
			),
			'medal' => array(
				'class' => 'SRV:medal.srv.condition.do.PwMedalLikeDo',
				'method' => 'addLike',
				'loadway' => 'load'
			)
		)
	),
	's_PwUserTagRelationDao_deleteRelation' => array(
		'description' => '删除用户标签的关系，调用',
		'param' => array('@param int $tag_id 标签id', '@return void'),
		'interface' => '',
		'list' => array(
			'PwUserTag' => array(
				'class' => 'SRV:usertag.dao.PwUserTagDao',
				'method' => 'updateTag',
				'loadway' => 'loadDao'
			),
		)
	),
	's_PwUserTagDao_deleteTag' => array(
		'description' => '删除用户标签时，调用',
		'param' => array('@param int $tag_id 标签id', '@return void'),
		'interface' => '',
		'list' => array(
			'PwUserTagRelation' => array(
				'class' => 'SRV:usertag.dao.PwUserTagRelationDao',
				'method' => 'deleteRelationByTagid',
				'loadway' => 'loadDao'
			),
		)
	),
	's_PwUserTagDao_batchDeleteTag' => array(
		'description' => '批量删除用户标签时，调用',
		'param' => array('@param array $tag_ids 标签id序列', '@return void'),
		'interface' => '',
		'list' => array(
			'PwUserTagRelation' => array(
				'class' => 'SRV:usertag.dao.PwUserTagRelationDao',
				'method' => 'batchDeleteRelationByTagids',
				'loadway' => 'loadDao'
			),
		)
	),
	's_PwUserTagRelation_batchDeleteRelation' => array(
		'description' => '删除用户标签关系的时候',
		'param' => array('@param array $tag_ids ', '@param PwUserTagRelation ', '@return void'),
		'interface' => '',
		'list' => array(
			'PwDeleteRelationDoUpdateTag' => array(
				'class' => 'SRV:usertag.srv.do.PwDeleteRelationDoUpdateTag',
				'method' => 'batchDeleteRelation',
				'loadway' => 'load'
			),
		)
	),
	's_PwUserTagRelation_deleteRelationByUid' => array(
		'description' => '根据用户ID删除用户标签关系',
		'param' => array('@param int $uid ', '@return void'),
		'interface' => '',
		'list' => array(
			'PwDeleteRelationDoUpdateTag' => array(
				'class' => 'SRV:usertag.srv.do.PwDeleteRelationDoUpdateTag',
				'method' => 'deleteRelationByUid',
				'loadway' => 'load'
			),
		)
	),
	's_PwUserTagRelation_batchDeleteRelationByUids' => array(
		'description' => '根据用户ID列表批量删除用户标签关系',
		'param' => array('@param array $uid ', '@return void'),
		'interface' => '',
		'list' => array(
			'PwDeleteRelationDoUpdateTag' => array(
				'class' => 'SRV:usertag.srv.do.PwDeleteRelationDoUpdateTag',
				'method' => 'batchDeleteRelationByUids',
				'loadway' => 'load'
			),
		)
	),
	/*添加表情*/
	's_PwEmotionDao_add' => array(
		'description' => '添加表情时，调用',
		'param' => array('@param int $id id', '@param array $fields 字段信息', '@return void'),
		'interface' => '',
		'list' => array(
			'addEmotion' => array(
				'class' => 'SRV:emotion.srv.PwEmotionService',
				'method' => 'updateCache',
				'loadway' => 'load'
			),
		)
	),
	/*编辑表情*/
	's_PwEmotionDao_update' => array(
		'description' => '编辑表情时，调用',
		'param' => array('@param int $id 表情id', '@param array $fields 字段信息', '@param array $increaseFields 字段信息', '@return void'),
		'interface' => '',
		'list' => array(
			'addEmotion' => array(
				'class' => 'SRV:emotion.srv.PwEmotionService',
				'method' => 'updateCache',
				'loadway' => 'load'
			),
		)
	),
	/*删除表情*/
	's_PwEmotionDao_delete' => array(
		'description' => '删除表情时，调用',
		'param' => array('@param int $id 表情id', '@return void'),
		'interface' => '',
		'list' => array(
			'addEmotion' => array(
				'class' => 'SRV:emotion.srv.PwEmotionService',
				'method' => 'updateCache',
				'loadway' => 'load'
			),
		)
	),
	's_PwEmotionDao_deleteEmotionByCatid' => array(
		'description' => '删除一组表情时，调用',
		'param' => array('@param int $cateId 表情组id', '@return void'),
		'interface' => '',
		'list' => array(
			'addEmotion' => array(
				'class' => 'SRV:emotion.srv.PwEmotionService',
				'method' => 'updateCache',
				'loadway' => 'load'
			),
		)
	),
	's_PwConfigDao_update' => array(
		'description' => '全局配置更新时，调用',
		'param' => array('@param string $namespace 配置域'),
		'interface' => '',
		'list' => array(
			'configCache' => array(
				'class' => 'SRV:config.srv.PwConfigService',
				'method' => 'updateConfig',
				'loadway' => 'load'
			),
		)
	),
	's_PwThreadType' => array(
		'description' => '获取帖子扩展类型时，调用',
		'param' => array('@param array $tType 帖子类型', '@return array'),
		'interface' => '',
		'list' => array(
			/*
			'debate' => array(
				'class' => 'HOOK:PwThreadType.PwThreadTypeDoDebate',
				'method' => 'getTtype',
				'loadway' => 'load'
			)*/
		)
	),
	's_punch' => array(
		'description' => '打卡时，调用',
		'param' => array('@param PwUserInfoDm $dm', '@return void'),
		'interface' => '',
		'list' => array(
			'task' => array(
				'expression' => 'config:site.task.isOpen==1',
				'class' => 'SRV:task.srv.condition.PwTaskMemberPunchDo',
				'method' => 'doPunch',
				'loadway' => 'load'
			)
		)
	),
	/*扩展存储类型*/
	's_PwStorage_getStorages' => array( //todo
		'description' => '获取附件存储类型',
		'param' => array('@param array $storages', '@return array'),
		'interface' => '',
		'list' => array(
		)
	),
	's_PwThreadManageDoCopy' => array( //todo
		'description' => '帖子复制',
		'param' => array('@param PwThreadManage $srv', '@return void'),
		'interface' => 'PwThreadManageCopyDoBase',
		'list' => array(
			'poll' => array(
				'class' => 'SRV:forum.srv.manage.do.PwThreadManageCopyDoPoll', 
				'method' => 'copyThread',
				'loadway' => 'load',
				'expression' => 'service:special==poll',
			), 
			'att' => array(
				'class' => 'SRV:forum.srv.manage.do.PwThreadManageCopyDoAtt', 
				'method' => 'copyThread',
				'loadway' => 'load',
				'expression' => 'service:ifupload!=0',
			),
		)
	),
	/* 用户退出之前的更新 */
	's_PwUserService_logout' => array(
		'description' => '退出登录',
		'param' => array('@param PwUserBo $loginUser', '@return void'),
		'interface' => 'PwLogoutDoBase',
		'list' => array(
			'updatelastvist' => array(
				'class' => 'SRV:user.srv.logout.do.PwLogoutDoUpdateLastvisit',
				'method' => 'beforeLogout',
				'loadway' => 'load'
			),
			'updateOnline' => array(
				'class' => 'SRV:online.srv.do.PwLogoutDoUpdateOnline',
				'method' => 'beforeLogout',
				'loadway' => 'load'
			),
		),
	),
	's_PwEditor_app' => array(
		'description' => '编辑器配置扩展',
		'param' => array('@param array $var', '@return array'),
		'list' => array(
		)
	),
	's_PwCreditOperationConfig' => array(
		'description' => '积分策略配置',
		'param' => array('@param array $config 积分策略配置', '@return array'),
		'list' => array(
		)
	),
	's_seo_config' => array(
		'description' => 'seo优化扩展',
		'param' => array('@param array $config seo扩展配置', '@return array'),
		'list' => array(
		)
	),
	's_PwUserBehaviorDao_replaceInfo' => array(
		'description' => '用户行为更新扩展',
		'param' => array('@param array $data 用户行为数据', '@return '),
		'list' => array(
			'task' => array(
				'class' => 'SRV:task.srv.PwTaskService',
				'method' => 'sendAutoTask',
				'loadway' => 'load',
				'expression' => 'config:site.task.isOpen==1',
			),
		),
	),
	's_admin_menu' => array(
		'description' => '后台菜单扩展',
		'param' => array('@param array $config 后台菜单配置', '@return array'),
		'list' => array(
		)
	),
	's_permissionCategoryConfig' => array(
		'description' => '用户组根权限',
		'param' => array('@param array $config 用户组根权限', '@return array'),
		'list' => array(
		)
	),
	's_permissionConfig' => array(
		'description' => '用户组权限',
		'param' => array('@param array $config 用户组权限', '@return array'),
		'list' => array(
		)
	),
	's_PwMobileService_checkVerify' => array( 
		'description' => '验证手机完成',
		'param' => array('@param int $mobile'),
		'list' => array(
		)
	),
	's_header_nav' => array(
		'description' => '全局头部导航',
		'param' => array(),
		'list' => array(
		)
	),
	's_header_info_1' => array(
		'description' => '头部用户信息扩展点1',
		'param' => array(),
		'list' => array(
		)
	),
	's_header_info_2' => array(
		'description' => '头部用户信息扩展点2',
		'param' => array(),
		'list' => array(
		)
	),
	's_header_my' => array(
		'description' => '头部帐号的下拉',
		'param' => array(),
		'list' => array(
		)
	),
	's_footer' => array(
		'description' => '全局底部',
		'param' => array(),
		'list' => array(
		)
	),
	's_space_nav' => array(
		'description' => '个人空间导航扩展',
		'param' => array('@param array $space', '@param string $src'),
		'list' => array(
		)
	),
	's_space_profile' => array(
		'description' => '空间资料页面',
		'param' => array('@param array $space'),
		'interface' => '',
		'list' => array( //这个顺序别改，pd要求的
			'education' => array(
				'class' => 'SRV:education.srv.profile.do.PwSpaceProfileDoEducation', 
				'method' => 'createHtml'
			),
			'work' => array(
				'class' => 'SRV:work.srv.profile.do.PwSpaceProfileDoWork', 
				'method' => 'createHtml'
			),
		)
	),
	's_profile_menus' => array (
		'description' => '个人设置-菜单项扩展',
		'param' => array('@param array $config 注册的菜单', '@return array'),
		'list' => array(
		),
	),

	's_attachment_watermark' => array(
		'description' => '全局->水印设置->水印策略扩展',
		'param' => array('@param array $config 已有的需要设置的策略,每一个扩展项格式:key=>title', '@return array'),
		'list' => array(
		),
	),
	's_verify_showverify' => array(
		'description' => '全局->验证码->验证策略',
		'param' => array('@param array $config 需要设置的策略,每一个扩展项格式:key=>title', '@return array'),
		'list' => array(
		),
	),
	/*手机短信扩展*/
	's_PwMobileService_getPlats' => array(
		'description' => '手机短信 - 平台选择',
		'param' => array('@param array $config 配置文件，可参考SRV:mobile.config.plat.php', '@return array'),
		'list' => array(
		),
	),
);