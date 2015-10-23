<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * 数据源接口
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @license http://www.phpwind.com
 * @version $Id: iPwDataSource2.php 5976 2012-03-15 03:16:10Z jieyin $
 * @package forum
 */
interface iPwDataSource2 {
	public function getData($ids);
}