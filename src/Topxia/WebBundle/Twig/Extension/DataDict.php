<?php
namespace Topxia\WebBundle\Twig\Extension;

class DataDict
{
	private static $dict = array(
		'courseStatus' => array(
			'draft' => '未发布',
			'published' => '已发布',
			'closed' => '未发布'
		),
		'courseStatus:html' => array(
			'draft' => '<span class="text-muted">未发布</span>',
			'published' => '<span class="text-success">已发布</span>',
			'closed' => '<span class="text-danger">未发布</span>'
		),
		'activityExpired' => array(
			'0' => '开放报名中',
			'1' => '已关闭报名'
		),
		'activityExpired:html' => array(
			'0' => '<span class="text-success">开放报名中</span>',
			'1' => '<span class="text-danger">已关闭报名</span>'
		),
		'activityRecommended' => array(
			'0' => '未置顶',
			'1' => '已置顶'
		),
		'activityRecommended:html' => array(
			'0' => '<span class="text-muted">未置顶</span>',
			'1' => '<span class="text-danger">已置顶</span>'
		),
		'couponType' => array(
			'minus' => '抵价',
			'discount' => '打折'
		),
		'couponStatus' => array(
			'used' => '已使用',
			'unused' => '未使用'
		),
		'fileType' => array(
			'video' => '视频',
			'audio' => '音频',
			'document' => '文档',
			'image' => '图片',
			'other' => '其他'
		),
		'fileType:html' => array(
			'video' => '<span class="glyphicon glyphicon-facetime-video text-success">视频</span>',
			'audio' => '<span class="glyphicon glyphicon-music text-success">音频</span>',
			'document' => '<span class="glyphicon glyphicon-briefcase text-success">文档</span>',
			'image' => '<span class="glyphicon glyphicon-picture text-success">图片</span>',
			'other' => '<span class="glyphicon glyphicon-question-sign text-success">其他</span>',
		),
		'orderStatus' => array(
			'created' => '未付款',
			'paid' => '已付款',
			'refunding' => '退款中',
            'refunded' => '已退款',
			'cancelled' => '已关闭',
		),
		'orderStatus:html' => array(
			'created' => '<span class="text-muted">未付款</span>',
			'paid' => '<span class="text-success">已付款</span>',
			'refunding' => '<span class="text-warning">退款中</span>',
            'refunded' => '<span class="text-danger">已退款</span>',
			'cancelled' => '<span class="text-muted">已关闭</span>',
		),
		'refundStatus' => array(
			'created'  => '已申请',
			'success' => '退款成功',
			'failed' => '退款失败',
			'cancelled' => '已取消',
		),
		'refundStatus:html' => array(
			'created'  => '<span class="text-warning">已申请</span>',
			'success' => '<span class="text-success">退款成功</span>',
			'failed' => '<span class="text-danger">退款失败</span>',
			'cancelled' => '<span class="text-muted">已取消</span>',
		),
		'payment' => array(
			'alipay' => '支付宝'
		),
		'moneyRecordType' => array(
			'income' => '充值',
			'payout' => '消费',
		),
		'threadType' => array(
			'discussion'=> '话题',
			'question' => '问答',
		),
		'contentType' => array(
            'article' => '文章',
            'activity' => '活动',
            'page' => '页面',
        ),
        'contentTemplateType' => array(
            'default' => '含菜单栏页面模板',
            'ad' => '弹出广告页模板',
            'page' => '页面',
        ),
	    'articleType' => array(
            'article' => '文章',
            'activity' => '活动',
            'page' => '文章',
        ),   
        'articleProperty' => array(
            'featured' => '头条',
            'promoted' => '推荐',
            'sticky' => '置顶',
        ),
        'dateType' => array(
            'today' => '今日',
            'yesterday' => '昨日',
            'this_week' => '本周',
            'last_week' => '上周',
            'this_month' => '本月',
            'last_month' => '上月',
    	),
        'contentStatus' => array(
            'published' => '已发布',
            'unpublished' => '未发布',
            'trash' => '回收站',
    	),
        'contentStatus:html' => array(
            'published' => '<span class="text-success">已发布</span>',
            'unpublished' => '<span class="text-muted">未发布</span>',
            'trash' => '<span class="text-warning">回收站</span>',
    	),
		'articleStatus' => array(
            'published' => '已发布',
            'unpublished' => '未发布',
            'trash' => '回收站',
    	),
        'articleStatus:html' => array(
            'published' => '<span class="text-success">已发布</span>',
            'unpublished' => '<span class="text-muted">未发布</span>',
            'trash' => '<span class="text-warning">回收站</span>',
    	),
    	'lessonType'=> array(
    		'video' => '视频',
    		'audio' => '音频',
    		'text' => '图文'
		),
		'userRole' => array(
			'ROLE_USER' => '学员',
			'ROLE_TEACHER' => '教师',
			'ROLE_ADMIN' => '管理员',
			'ROLE_SUPER_ADMIN' => '超级管理员'
		),
		'memberLevel' => array(
			'1' => '银牌会员',
			'2' => '金牌会员',
			'3' => '钻石会员'
		),
		'duration_unit' => array(
			'month' => '个月',
			'year' => '年'
		),
		'boughtType' => array(
			'new' => '购买',
			'renew' => '续费',
			'upgrade' => '升级',
			'edit' => '编辑',
			'cancel' => '取消会员'
		),
		'userKeyWordType' => array(
			'nickname' => '用户名',
			'email' => '邮件地址',
			'loginIp' => '登录IP'
		),
		'logLevel' => array(
			'info' => '提示',
			'warning' => '警告',
			'error' => '错误'
		),
		'logLevel:html' => array(
			'info' => '<span>提示</span>',
			'warning' => '<span class="text-warning">警告</span>',
			'error' => '<span class="text-danger">错误</span>'
		),
		'userType' => array(
			'default' => '网站注册',
			'weibo' => '微博登录',
			'renren' => '人人连接',
			'qq' => 'QQ登录',
			'douban' => '豆瓣连接'
		),
		'questionType' => array(
	    	'single_choice' => '单选题',
	    	'choice' => '多选题',
            'uncertain_choice' => '不定项选择题',
	    	'fill' => '填空题',
	    	'determine' => '判断题',
	    	'essay' => '问答题',
	    	'material' => '材料题',
        ),
        'difficulty' => array(
	    	'simple' => '简单',
	    	'normal' => '一般',
	    	'difficulty' => '困难',
        ),
        'targetName' => array(
        	'course' => '课程',
        	'vip' => '会员'
    	),

        'commissionStatus' => array(
        	'created' =>            '不可提款',
			'paid' =>               '可提款',
			'applying' =>           '审核提款中',
            'refused' =>            '提款审核不通过',
			'cancelled' =>          '已取消申请提款',
			'moneydrawed ' =>       '已提款',
			'frozen' =>            '冻结状态',
		),
		'commissionStatus:html' => array(
			'created' =>            '<span class="text-muted">不可提款</span>',
			'paid' =>               '<span class="text-success">可提款</span>',
			'applying' =>           '<span class="text-muted">审核提款中</span>',
            'refused' =>            '<span class="text-danger">提款审核不通过</span>',
			'cancelled' =>          '<span class="text-muted">已取消申请提款</span>',
			'moneydrawed ' =>       '<span class="text-muted">已提款</span> ',
			'frozen' =>            '<span class="text-danger">冻结状态</span> ',
		),

		'saleType' => array(
        	'linksale-web' =>             '全站链接推广',
			'linksale-course' =>          '课程链接推广',
			'offsale-course' =>           '课程优惠码推广',
            'invite-course' =>            '课程邀请码推广',
            'invite-web' =>               '注册邀请码推广',
			
		),
		'saleType:html' => array(
			'linksale-web' =>            '<span class="text-muted">全站链接推广</span>',
			'linksale-course' =>               '<span class="text-success">课程链接推广</span>',
			'offsale-course' =>           '<span class="text-muted">课程优惠码推广</span>',
            'invite-course' =>            '<span class="text-danger">课程邀请码推广</span>',
			'invite-web' =>          '<span class="text-muted">注册邀请码推广</span>',
			
		),
		'offsaleType' => array(
			'offsale-course' =>           '课程优惠码推广',
            'invite-course' =>            '课程邀请码推广',
            'invite-web' =>               '注册邀请码推广',
		),
		
		'linksaleType' => array(
			'linksale-course' =>         '课程链接推广',
            'linksale-web' =>            '全站链接推广',			
		),

		'adShowModeType' => array(
			'0' =>         '弹窗',
            '1' =>         '顶栏',			
		),


		'degreeType' => array(
			'小学' =>            '小学',
        	'初中' =>            '初中',	
        	'高中' =>            '高中',	
        	'大专' =>            '大专',	
        	'本科' =>            '本科',	
        	'硕士' =>            '硕士',	
        	'博士' =>            '博士',
        				
		),

		'genderType' => array(
			'male' =>            '男',
        	'female' =>            '女',	
        	
       
        				
		),

	);

	public static function dict($type)
	{
		return isset(self::$dict[$type]) ? self::$dict[$type] : array();
	}

	public static function text($type, $key)
	{
		if (!isset(self::$dict[$type])) {
			return null;
		}

		if (!isset(self::$dict[$type][$key])) {
			return null;
		}

		return self::$dict[$type][$key];
	}

}