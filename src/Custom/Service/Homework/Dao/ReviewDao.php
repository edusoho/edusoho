<?php

namespace Custom\Service\Homework\Dao;

interface ReviewDao
{

	/**
	 * 获取课程相关的其它期课程.
	 * @param course 课程对象.
	 * @return 相关课程列表.
	**/
	public function findOtherPeriods($course);
}