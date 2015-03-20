<?php
return array(
	'999' => array(
		'method' => 'test',
		'args' => array('testdata'),
		'alias' => '通讯测试',
		'description' => '通讯测试接口'
	),
	'101' => array(
		'method' => 'addUser',
		'args' => array('uid'),
		'alias' => '用户  %s  注册',
		'description' => '注册用户'
	),
	'111' => array(
		'method' => 'synLogin',
		'args' => array('uid'),
		'alias' => '%s 同步登录',
		'description' => '同步登录'
	),
	'112' => array(
		'method' => 'synLogout',
		'args' => array('uid'),
		'alias' => ' %s 同步登出',
		'description' => '同步登出'
	),
	'201' => array(
		'method' => 'editUser',
		'args' => array('uid', 'changepwd'),
		'alias' => '修改  %s  用户信息',
		'description' => '编辑用户基本信息(用户名，密码，邮箱，安全问题)'
	),
	'202' => array(
		'method' => 'editUserInfo',
		'args' => array('uid'),
		'alias' => '修改  %s  详细资料',
		'description' => '修改用户详细资料'
	),
	'203' => array(
		'method' => 'uploadAvatar',
		'args' => array('uid'),
		'alias' => '上传  %s  头像',
		'description' => '上传用户头像 '
	),
	'211' => array(
		'method' => 'editCredit',
		'args' => array('uid'),
		'alias' => '修改  %s  积分',
		'description' => '修改用户积分'
	),
	'222' => array(
		'method' => 'editMessageNum',
		'args' => array('uid'),
		'alias' => '修改  %s  未读消息',
		'description' => '修改未读消息'
	),
	'301' => array(
		'method' => 'deleteUser',
		'args' => array('uid'),
		'alias' => '删除  %s',
		'description' => '删除用户'
	),
	'402' => array(
		'method' => 'setCredits',
		'args' => array(),
		'alias' => '修改积分配置',
		'description' => '修改积分配置',
	),
	'403' => array(
		'method' => 'alterAvatarUrl',
		'args' => array(),
		'alias' => '修改头像链接',
		'description' => '修改头像链接'
	),
);
?>