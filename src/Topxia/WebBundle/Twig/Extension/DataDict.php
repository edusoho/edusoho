<?php
namespace Topxia\WebBundle\Twig\Extension;

class DataDict
{
	private static $dict = array(
		'courseStatus' => array(
			'draft' => '未发布',
			'published' => '已发布',
			'closed' => '已关闭'
		),
		'courseStatus:html' => array(
			'draft' => '<span class="text-muted">未发布</span>',
			'published' => '<span class="text-success">已发布</span>',
			'closed' => '<span class="text-danger">已关闭</span>'
		),

		'contentType' => array(
            'article' => '文章',
            'activity' => '活动',
            'page' => '页面',
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
    	'lessonType'=> array(
    		'video' => '视频',
    		'audio' => '音频',
    		'text' => '图文'
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