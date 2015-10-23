<?php
Wind::import('WIND:viewer.exception.WindViewException');
/**
 * 视图渲染器接口类
 * 
 * 视图渲染器接口,主要定义了两个接口方法<i>windAssign</i>和<i>windFetch</i><pre>
 * IWindViewerResolver接口是框架定义的基础的视图渲染器接口,通过实现该接口类来自定义视图渲染器
 * <i>WindViewerResolver</i>类是该接口的基本实现
 * </pre>
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: IWindViewerResolver.php 3904 2013-01-08 07:01:26Z yishuo $
 * @package viewer
 */
interface IWindViewerResolver {

	/**
	 * 设置视图变量设置进当前模板
	 * 
	 * @param array|string|object $vars
	 * @param string $key 可选 默认值为空
	 * @return void
	 */
	public function windAssign($vars, $key = '');

	/**
	 * 获取模板内容与变量信息
	 * 
	 * @param string $template 可选 默认值为空
	 * @return void
	 */
	public function windFetch($template = '');

}

/**
 * 辅助WindViewerResolver完成视图渲染工作
 * 
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: IWindViewerResolver.php 3904 2013-01-08 07:01:26Z yishuo $
 * @package viewer
 */
class WindRender {

	/**
	 * 视图渲染
	 * 
	 * @param string $__tpl
	 * @param array $__vars
	 * @param WindViewerResolver $__viewer
	 * @return void
	 * @throws WindViewException
	 */
	public static function render($__tpl, $__vars, $__viewer) {
		@extract($__vars, EXTR_REFS);
		if (!include ($__tpl)) {
			throw new WindViewException('[viewer.WindRender.render] template name ' . $__tpl, 
				WindViewException::VIEW_NOT_EXIST);
		}
	}
}