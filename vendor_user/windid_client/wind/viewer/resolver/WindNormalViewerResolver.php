<?php
Wind::import('WIND:viewer.IWindViewerResolver');
/**
 * 通用视图解析渲染
 *
 * @author Qiong Wu <papa0924@gmail.com> 2011-12-19
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package viewer
 * @subpackage resolver
 */
class WindNormalViewerResolver extends WindModule implements IWindViewerResolver {
	protected $vars = array();
	/**
	 * @var windView
	 */
	protected $windView = null;

	/* (non-PHPdoc)
	 * @see IWindViewerResolver::windAssign()
	 */
	public function windAssign($vars, $key = '') {
		if ($key === '')
			$this->vars = $vars;
		elseif (!isset($this->vars[$key]))
			$this->vars[$key] = $vars;
	}

	/* (non-PHPdoc)
	 * @see IWindViewerResolver::windFetch()
	 */
	public function windFetch($template = '') {
		$template || $template = $this->windView->templateName;
		if (!$template) return '';
		list($template) = $this->windView->getViewTemplate($template);
		WindRender::render($template, $this->vars, $this);
	}

	/**
	 * @return WindView
	 */
	public function getWindView() {
		return $this->windView;
	}

	/**
	 * @param WindView $windView
	 * @return void
	 */
	public function setWindView($windView) {
		$this->windView = $windView;
	}
}

?>