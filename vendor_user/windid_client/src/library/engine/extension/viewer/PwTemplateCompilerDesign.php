<?php
Wind::import('WIND:viewer.AbstractWindTemplateCompiler');
/**
 * 门户调用标签解析
 * 
 * <design role="" id=""></design>
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $id$
 * @package engine.extension.viewer
 */
class PwTemplateCompilerDesign extends AbstractWindTemplateCompiler {

	protected $id;
	protected $role;
	protected $pk;
	protected $service;
	
	private $_uri;
	private $_router;
	/* (non-PHPdoc)
	 * @see AbstractWindTemplateCompiler::compile()
	 */
	public function compile($key, $content) {
		if (!$this->role) return '';
		if (!$this->_beforeDesign() && !in_array($this->role, array('module','script'))) return '';
		$this->service->setCompileMode($this->role);
		switch ($this->role) {
			case 'start':
				$viewTemplate = Wind::getComponent('template');
				$data = $this->service->startDesign($this->pk, $this->_uri);
				return $viewTemplate->compileStream($data, $this->windViewerResolver);
			case 'segment':
				$viewTemplate = Wind::getComponent('template');
				$data = $this->service->compileSegment($this->id);
				return $viewTemplate->compileStream($data, $this->windViewerResolver);
			case 'data':
				return $this->service->compileData($this->id);
			case 'module':
				$viewTemplate = Wind::getComponent('template');
				$data = $this->service->compileModule($this->id);
				return $viewTemplate->compileStream($data, $this->windViewerResolver);
			case 'script':
				return $this->service->compileScript();
			case 'title':
				return $this->service->compileTitle($this->id);
			case 'tips':
				return $this->service->compileTips($this->id);
			case 'end':
				//return $this->service->afterDesign();
		}
	}
	

	public function getProperties() {
		return array('id', 'role', 'pagename');
	}
	
	private function _beforeDesign() {
		Wind::import('SRV:design.srv.PwDesignCompile');
		$this->service = PwDesignCompile::getInstance();
		//$this->service = Wekit::load('design.srv.PwDesignCompile');
		//纠结的template标签
		if (!isset($this->_router)) { 
			$this->_router();
			list($pageName, $unique) = $this->_pageName();
			if (!$pageName && !$unique) return false;
			$this->service->setIsDesign($this->getRequest()->getPost('design'));
			$this->pk = $unique ? $this->getRequest()->getGet($unique) : '';
			$this->service->beforeDesign($this->_router, $pageName, $this->pk);
			$this->service->setPermission();
		}
		return true;
	}
	
	private function _router() {
		$router = Wind::getComponent('router');
    	$m = $router->getModule(); 
    	$c = $router->getController(); 
    	$a = $router->getAction();
    	$this->_router = $m.'/'.$c.'/'.$a;
    	$this->_uri = urlencode($router->request->getHostInfo() .$router->request->getRequestUri());
	}
	
	private function _pageName() {
		$sysPage = Wekit::load('design.srv.router.PwDesignRouter')->get();
		if ($this->_router && isset($sysPage[$this->_router])){ 
			return $sysPage[$this->_router];
		}
		return array();
	}
	
}

?>