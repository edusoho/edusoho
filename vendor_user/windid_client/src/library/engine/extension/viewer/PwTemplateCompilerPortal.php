<?php
Wind::import('WIND:viewer.AbstractWindTemplateCompiler');
Wind::import('SRV:design.srv.PwPortalCompile');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwTemplateCompilerPortal.php 25125 2013-03-05 03:29:29Z gao.wanggao $ 
 * @package 
 */
class PwTemplateCompilerPortal extends AbstractWindTemplateCompiler {
	
	protected $srv;
	private $_url;
	private $_router;
	
	public function compile($key, $content) {
		$viewTemplate = Wind::getComponent('template');
		$this->_router();
		list($pageName, $unique) = $this->_pageName();
		if (!$pageName && !$unique) {
			$content = str_replace('<pw-start/>', '', $content);
			$content = str_replace('<pw-end/>', '', $content);
			return $viewTemplate->compileStream($content, $this->windViewerResolver);
		}	
		foreach ($this->windViewTemplate->getCompiledBlockData() as $key => $value) {
            $content = str_replace('#' . $key . '#', ($value ? $value : ''), $content);
        }
		Wind::import('SRV:design.srv.PwDesignCompile');
		$this->srv = PwDesignCompile::getInstance();
		//$this->srv = Wekit::load('design.srv.PwDesignCompile');
		$this->srv->setIsDesign($this->getRequest()->getPost('design'));
		$_pk = $unique ? $this->getRequest()->getGet($unique) : '';
		$this->srv->beforeDesign($this->_router, $pageName, $_pk);
		$pageBo = $this->srv->getPageBo();
		$this->srv->setPermission();
		
		//对模版进行编译
		$portalSrv = new PwPortalCompile($pageBo);
		$isPortalCompile = $this->srv->isPortalCompile();
		
		//对自定义页编辑
		if ($isPortalCompile == 1) {
			$content = $portalSrv->compilePortal($content);
		}
		
		//对系统页编辑
		if ($isPortalCompile == 2) {
			$content = $portalSrv->compileTpl($content, true);
		} else {
			$content = $portalSrv->compileTpl($content);
		}
	
		//$content = $portalSrv->doCompile($content);
		//转换Pw标签
		$content = $this->compileStart($content, $_pk, $this->_url);
			
		$content = $this->compileSign($content);
		$content = $this->compileTemplate($content); //自定义页
		$content = $this->compileDrag($content);
		$content = $this->compileTitle($content);
		$content = $this->compileList($content);
		$content = $this->compileCss($content, $pageBo);
		$content = $this->compileEnd($content, $pageBo);
		$content =  $viewTemplate->compileStream($content, $this->windViewerResolver);
		/*if ($isPortalCompile > 0) {
			$this->srv->refreshPage();
		}*/
		return $content;
	}
	
	protected function compileStart($content, $pk, $url) {
		$start = $this->srv->startDesign($pk, $url);
		return str_replace('<pw-start/>', $start, $content);
	}
	
	protected function compileSign($content) {
		$this-> _getFooter();
		$in = array(
			'<pw-head/>',
			'<pw-navigate/>',
			'<pw-footer/>',
		);
		$out = array(
			'<!--# if($portal[\'header\']){ #--><template source=\'TPL:common.header\' load=\'true\' /><!--# } #-->',
			'<!--# if($portal[\'navigate\']){ #--><div class="bread_crumb">{@$headguide|html}</div><!--# } #-->',
			'<!--# if($portal[\'footer\']){ #--><template source=\'TPL:common.footer\' load=\'true\' /><!--# } #-->',
		);
		
		return str_replace($in, $out, $content);
	}
	
	protected function compileCss($content, $pageBo) {
		$dir = $pageBo->getTplPath();
		$url =  WindUrlHelper::checkUrl(PUBLIC_THEMES . '/portal/local/' . $dir, PUBLIC_URL);
		if (preg_match_all('/\{@G:design.url.(\w+)}/isU',$content, $matches)) {
			foreach ($matches[1] AS $k=>$v) {
				if (!$v) continue;
				$replace = $url . '/' . $v;
	    		$content = str_replace($matches[0][$k], $replace, $content);
    		}
		}			
		return $content;
	}
	
	protected function compileTitle($content) {
		if (preg_match_all('/\<pw-title\s*id=\"(\w+)\"\s*[>|\/>](.+)<\/pw-title>/isU',$content, $matches)) {
			foreach ($matches[1] AS $k=>$v) {
				if (!$v) continue;
    			$title = $this->srv->compileTitle($v);
	    		$content = str_replace($matches[0][$k], $title, $content);
    		}
		}
		return $content;
	}
	
	protected function compileList($content) {
		if (preg_match_all('/\<pw-list\s*id=\"(\d+)\"\s*[>|\/>](.+)<\/pw-list>/isU',$content, $matches)) {
			foreach ($matches[1] AS $k=>$v) {
				if (!$v) continue;
    			$list = $this->srv->compileList($v);
	    		$content = str_replace($matches[0][$k], $list, $content);
    		}
		}
		return $content;
	}
	
	protected function compileDrag($content) {
		if (preg_match_all('/\<pw-drag\s*id=\"(\w+)\"\s*\/>/isU',$content, $matches)) {
			foreach ($matches[1] AS $k=>$v) {
				if (!$v) continue;
    			$segment = $this->srv->compileSegment($v);
	    		$content = str_replace($matches[0][$k], $segment, $content);
    		}
		}
		return $content;
	}
	
	/**
	 * 必须放在转换的最后一步
	 */
	protected function compileEnd($content) {
		//$viewTemplate = Wind::getComponent('template');
		$this->srv->afterDesign(); 
		return str_replace('<pw-end/>', '', $content);
		//return $viewTemplate->compileStream($content, $this->windViewerResolver);
	}
	
	/**
	 * 兼容框架怪异的template标签
	 * Enter description here ...
	 * @param unknown_type $content
	 */
	protected function compileTemplate($content) {
		if (preg_match_all('/\<template\s*source=[\'|\"](.+)[\'|\"](.+)\/>/isU',$content, $matches)) {
			foreach ($matches[1] AS $k=>$v) {
				$content = str_replace($matches[0][$k], $this->_getTemplate($v), $content);
    		}
    		$content = $this->compileTemplate($content);
		}
		$content = $this->comileSegment($content);
		return $content;
	}
	
	/**
	 * 对公共文件的design segment进行转换
	 * Enter description here ...
	 */
	protected function comileSegment($content) {
		if(preg_match_all('/\<design\s*role=\"segment\"\s*id=\"(.+)\"\s*\/>/isU', $content, $matches)) {
			foreach ($matches[1] AS $k=>$v) {
				$content = str_replace($matches[0][$k], '<pw-drag id="'.$v.'"/>', $content);
			}
		}
		return $content;
	}
	
	private function _getTemplate($path) {
		list($tpl, $compile) = $this->windViewerResolver->getWindView()->getViewTemplate($path);
		if (!$tpl) return '';
		return  WindFile::read($tpl);
	}
	
	private function _router() {
		$router = Wind::getComponent('router');
    	$m = $router->getModule(); 
    	$c = $router->getController(); 
    	$a = $router->getAction();
    	$this->_router = $m.'/'.$c.'/'.$a;
    	$this->_url = urlencode($router->request->getHostInfo() .$router->request->getRequestUri());
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