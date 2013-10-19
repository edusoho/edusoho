<?php
/**
 * 视图处理器接口类
 * 
 * <i>WindView</i>是基础的视图处理器,职责：进行视图渲染.<br>
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: IWindView.php 2973 2011-10-15 19:22:48Z yishuo $
 * @package viewer
 */
interface IWindView {

	/**
	 * 视图渲染方法
	 * 
	 * 通过该方法进行视图渲染,并将视图内容设置到response对象中以备输出<code>
	 * $this->getResponse()->setBody($viewContent, $templateName);
	 * </code>
	 * @return void
	 * @throws WindViewException
	 */
	public function render();

}

?>