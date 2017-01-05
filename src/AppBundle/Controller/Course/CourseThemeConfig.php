<?php
namespace AppBundle\Controller\Course;

class CourseThemeConfig
{
	public static $template = "";

	public static function getBoughtConfig()
	{
		return array(
			'header' => '',
			'tabs'	=> array(),
			'widgets' => array(),
		);
	}

	public static function getShowConfig()
	{
		return array(
			'header' => '',
			'tabs'	=> array(),
			'widgets' => array(),
		);
	}
}