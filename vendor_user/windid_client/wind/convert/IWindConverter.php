<?php
/**
 * 编码转化器通用接口定义
 * 
 * @author Qiong Wu <papa0924@gmail.com> 2011-10-19
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: IWindConverter.php 3016 2011-10-20 02:23:08Z yishuo $
 * @package convert
 */
interface IWindConverter {

	/**
	 * 编码转化
	 * 
	 * 对输入的字符串进行从原编码到目标编码的转化,请确定原编码与目标编码
	 * @param string $srcText
	 * @return string 转化后的编码
	 */
	public function convert($str);
}

?>