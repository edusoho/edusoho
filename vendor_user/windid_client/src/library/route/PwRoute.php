<?php
Wind::import('LIB:route.AbstractPwRoute');
/**
 * 前台路由
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: PwRoute.php 25816 2013-03-25 06:10:30Z long.shi $
 * @package library
 */
class PwRoute extends AbstractPwRoute {
	
	private $entrance = 'CONF:entrance';
	/**
	 * 特殊规则的rewrite
	 */
	private $rewrite_special = false;
	
	/**
	 * 普通规则的rewrite
	 */
	private $rewrite_common = false;
	
	/**
	 * 符合特殊情况时，url串省略mca参数
	 */
	private $omit_mca = false;
	public $dynamicDomain = array();
	public $dynamic = array();
	public $dynamicHost = '';
	/**
	 * 符合特殊二级域名时,后面皆可省略的情况
	 */
	private $onlydomain = false;
	protected $params = array('a' => 3, 'c' => 2, 'm' => 1);
	protected $_init = false;
	protected $base = null;
	protected $origialBase = null;
	protected $scheme = 'http://';
	
	/*
	 * (non-PHPdoc) @see WindRewriteRoute::match()
	 */
	public function match($request) {
		$this->init(false, $request);
		$host = $request->getHostInfo();
		$args = $this->_matchDomain($host);
		$_args = $this->_matchScript($request->getScript(), $request->getScriptUrl());
		$path = trim(str_replace($request->getBaseUrl(true), '', $host . $request->getRequestUri()), '/');
		$rawPath = $this->_getRawPath($path);
		
		// 首页设置
		if (empty($rawPath) && empty($args) && empty($_args)) {
			return Wekit::C()->site->get('homeRouter', array());
		}
		$_args = $this->_matchPath($path) + $_args;
		if (empty($_args)) {
			$this->_filterIllegal($rawPath);
		}
		return $_args + $args;
	}
	
	/**
	 * 解析url - 公开方法
	 *
	 * 返回false 表示外链
	 * 返回array 为解析好的参数
	 *
	 * @param string $url        	
	 * @return array
	 */
	public function matchUrl($url) {
		Wind::import('SRV:domain.srv.helper.PwDomainHelper');
		$r = PwDomainHelper::parse_url($url);
		list($host, $isSecure, $script, $path, $scriptUrl) = $r;
		if ($host && !PwDomainHelper::isMyBrother($host, 
			Wind::getApp()->getRequest()->getHostInfo())) {
			return false;
		}
		$this->init();
		
		$args = $this->_matchDomain($host);
		$_args = $this->_matchScript($script, $scriptUrl);
		return array_merge($this->_getDefault(), $args, $_args, $this->_matchPath($path, true));
	}
	
	/*
	 * (non-PHPdoc) @see WindRewriteRoute::build()
	 */
	public function build($router, $action, $args = array()) {
		$this->init(true);
		list($_m, $_c, $_a, $args) = $this->_resolveMca($router, $action, $args);
		$domain = $this->_buildDomain($_m, $_c, $_a, $args);
		if ($this->onlydomain) return $domain;
		$url = $this->_buildUrl($_m, $_c, $_a, $router, $args);
		if (3 < count($this->dynamicDomain)) {
			$this->dynamicDomain[] = $url;
		}
		return $domain . $url;
	}
	
	/**
	 * 检查url绝对路径
	 *
	 * @param string $url
	 * @return string
	 */
	public function checkUrl($url) {
		if (false === strpos($url, '://')) {
			$appType = $this->_getAppType();
			$base = Wekit::url()->base;
			isset($appType['default']) && $base = $appType['default'] . $this->base;
			$url = $base . '/' . trim($url, '/');
		}
		return $url;
	}

	/**
	 * 解析url - 普通伪静态
	 *
	 * @param WindHttpRequest $request        	
	 * @param string $path        	
	 * @return array null
	 */
	private function _matchCommon($path) {
		$pattern = '/^(\w+)(-\w+)(-\w+)(.*)$/i';
		if (!preg_match($pattern, $path, $matches)) return array();
		$params = array();
		foreach ($this->params as $k => $v) {
			$value = isset($matches[$v]) ? $matches[$v] : '';
			$params[$k] = trim($value, '-/');
		}
		return array_merge($params, WindUrlHelper::urlToArgs(trim($matches[4], '?'), true));
	}

	/**
	 * 解析url - 二级域名
	 *
	 * @param WindHttpRequest $request        	
	 * @param string $path        	
	 * @return array null
	 */
	private function _matchDomain($host) {
		/* 解析二级域名 */
		if (empty($host)) return array();
		$appType = $this->_getAppType();
		if (isset($appType['default']) && $host == $appType['default']) return array();
		$space_root = Wekit::C()->site->get('domain.space.root', '');
		if ($space_root) {
			$rawHost = str_replace($this->scheme, '', $host);
			if ($pos = strpos($rawHost, $space_root)) {
				$uid = Wekit::load('domain.PwSpaceDomain')->getUidByDomain(substr($rawHost, 0, $pos - 1));
				if ($uid) {
					return array('m' => 'space', 'c' => 'index', 'a' => 'run', 'uid' => $uid);
				}
			}
		}
		$domain = Wekit::C()->site->get('domain', array());
		if (isset($domain[$host])) {
			list($a, $c, $m, $args) = WindUrlHelper::resolveAction($domain[$host]);
			return array_merge($args, array('m' => $m, 'c' => $c, 'a' => $a));
		}
		return array();
	}

	/**
	 * 解析脚本
	 *
	 * @param WindHttpRequest $request        	
	 * @return array
	 */
	private function _matchScript($script, $scriptUrl) {
		if (empty($script) || $script == 'index.php') {
			return array();
		}
		$multi = $this->_getMulti();
		if (isset($multi[$script])) {
			$this->base = rtrim(str_replace($script, '', $scriptUrl), '\\/.');
			$route = explode('/', $multi[$script]);
			return array('m' => $route[0], 'c' => $route[1], 'a' => $route[2]);
		} else {
			foreach ($multi as $k => $v) {
				if (false !== ($pos = strpos($scriptUrl, $k))) {
					$this->base = rtrim(substr($scriptUrl, 0, $pos), '\\/.');
					$route = explode('/', $v);
					return array('m' => $route[0], 'c' => $route[1]) + ($route[2] == '*' ? array() : array('a' => $route[2]));
				}
			}
		}
		return array();
	}

	/**
	 * 生成url - 普通伪静态
	 *
	 * @param array $route        	
	 * @param
	 *        	array WindRouter
	 * @param array $args        	
	 * @return string
	 */
	private function _buildCommon($router, $route, $args) {
		$reverse = '/%s-%s-%s';
		$_args = array();
		
		$flag = 0;
		$methods = array(
			'a' => 'getDefaultAction', 
			'c' => 'getDefaultController', 
			'm' => 'getDefaultModule');
		$flags = array('a' => 0, 'c' => 1, 'm' => 3);
		$consts = array('a' => 1, 'c' => 3, 'm' => 7);
		foreach ($this->params as $k => $v) {
			if ($route[$k] === $router->$methods[$k]() && $flag === $flags[$k]) $flag = $consts[$k];
			$_args[$v] = $route[$k];
			unset($args[$k]);
		}
		if ($flag == 7 && empty($args)) return '';
		$_args[0] = $reverse;
		ksort($_args);
		$url = call_user_func_array("sprintf", $_args);
		return $url . ($args ? '?' . WindUrlHelper::argsToUrl($args) : '');
	}

	/**
	 * 生成url - 二级域名
	 *
	 * @param array $route        	
	 * @param array $args        	
	 * @return string
	 */
	private function _buildDomain($_m, $_c, $_a, $args) {
		/* 二级域名 */
		$key = "$_m/$_c/$_a";
		list($domain, $type) = $this->_getDomainByType($key);
		if (!empty($domain)) {
			$domainKey = $this->_getDomainKey();
			if (isset($domainKey[$key])) {
				$id = $domainKey[$key];
				if (!isset($args[$id])) {
					return '';
				} else {
					if (isset($domain[$args[$id]])) {
						$this->omit_mca = true;
						1 == count($args) && $this->onlydomain = true;
						return $domain[$args[$id]] . $this->base;
					} elseif ($type == 'forum') {
						$this->dynamicDomain[] = '<?php $__route_domain=' . var_export($domain, 
							true) . ';';
						if (1 == count($args)) {
							$this->dynamicDomain[] = 'if($__route_domain[' . $args[$id] . ']){ echo $__route_domain[' . $args[$id] . '].\'' . $this->base . '\';}else{ ?>';
						} else {
							$this->dynamicDomain[] = 'if($__route_domain[' . $args[$id] . ']){ echo $__route_domain[' . $args[$id] . '].\'' . $this->base . '\';?>';
							$this->dynamicDomain[] = '<?php }else{ ?>';
						}
						$this->dynamicDomain[] = '<?php } ?>';
					}
				}
			}
		}
		return $this->_getModuleDomain($_m);
	}

	/**
	 * 生成url串
	 *
	 * @param string $_m        	
	 * @param string $_c        	
	 * @param string $_a        	
	 * @param WindRouter $router        	
	 * @param array $args        	
	 * @return string
	 */
	private function _buildUrl($_m, $_c, $_a, $router, $args) {
		if ($this->rewrite_special) {
			$rule = $this->_getRule();
			if (!empty($rule)) {
				$_args = $args;
				foreach ($rule as $v) {
					if ($v['route'] == "$_m/$_c/$_a") {
						$format = array();
						preg_match_all('/\{(\w+)\}/', $v['format'], $matches);
						//if (empty($matches[1])) continue;
						$is_fname = strpos($v['format'], '{fname}') !== false;
						if (1 === count($matches[1]) && !$is_fname) {
							if (!isset($_args[$matches[1][0]])) continue;
						}
						if ($is_fname) {
							if ($this->dynamicDomain) continue;
							if (!isset($_args['fid'])) continue;
							$domain = $this->_getDomain('id', 'domain');
							$this->dynamic[] = '<?php $__route_rewrite=' . var_export($domain, true) . ';';
							$this->dynamic[] = $_args['fid'] ? '($__route_rewrite[' . $_args['fid'] . '] ? $__route_rewrite[' . $_args['fid'] . '] : \'fname\')' : '\'fname\'';
							if (is_numeric($_args['fid'])) {
								if (!$domain[$_args['fid']]) continue;
								$format['{fname}'] = $domain[$_args['fid']];
							}
						}
						if ($pos = strpos($v['format'], '{page}')) {
							if (!isset($_args['page'])) {
								$v['format'] = str_replace($v['format'][$pos - 1] . '{page}', '', $v['format']);
							}
						}
						foreach ($matches[1] as $code) {
							if ($code != 'fname') {
								$format['{' . $code . '}'] = isset($_args[$code]) ? rawurlencode($_args[$code]) : '';
								unset($_args[$code]);
							}
						}
						if ('forum' == $this->_getType("$_m/$_c/$_a")) unset($_args['fid']);
						return '/' . trim(strtr($v['format'], $format), '/-.*') . ($_args ? '?' . WindUrlHelper::argsToUrl(
							$_args) : '');
					}
				}
			}
		}
		if ($this->rewrite_common) {
			return $this->_buildCommon($router, array('m' => $_m, 'c' => $_c, 'a' => $_a), $args);
		} else {
			/* 非rewrite时获取脚本文件 */
			$script = $this->_buildScript($_m, $_c, $_a, $args);
			$url = '';
			if ($this->omit_mca)
				$url = $args ? $script . '?' . WindUrlHelper::argsToUrl($args) : $script;
			else {
				$_args = array();
				if ($_m !== $router->getDefaultModule()) $_args['m'] = $_m;
				if ($_c !== $router->getDefaultController()) $_args['c'] = $_c;
				if ($_a !== $router->getDefaultAction()) $_args['a'] = $_a;
				$args = array_merge($_args, $args);
				$url = $args ? $script . '?' . WindUrlHelper::argsToUrl($args) : '';
			}
			return $url;
		}
	}

	/**
	 * 解析url串
	 *
	 * @param WindHttpRequest $request        	
	 * @param string $path        	
	 * @return array
	 */
	private function _matchPath($path, $rawDecode = false) {
		$path = trim($path, '/');
		if (empty($path)) return array();
		if ($this->rewrite_special) {
			/* 解析特殊伪静态 */
			$rule = $this->_getRule();
			if (!empty($rule)) {
				if (false !== strpos($path, '?')) {
					list($rewritePath, $queryPath) = explode('?', $path . '?', 2);
				} else {
					list($rewritePath, $queryPath) = explode('&', $path . '&', 2);
				}
				foreach ($rule as $k => $v) {
					if ($k == 'default') continue;
					$rewritePath = rawurldecode($rewritePath);
					if (preg_match($v['pattern'], $rewritePath, $matches)) {
						$matches = array_diff_key($matches, range(0, intval(count($matches) / 2)));
						$args = WindUrlHelper::urlToArgs(trim($queryPath, '?'), true);
						if ($k == 'thread' || $k == 'cate') {
							if (!isset($matches['fid']) && isset($matches['fname'])) {
								$domain = $this->_getDomain('domain', 'domain_key');
								if (isset($domain[$matches['fname']])) {
									list($_a, $_c, $_m, $_args) = WindUrlHelper::resolveAction(
										$domain[$matches['fname']]);
									return array_merge($matches, 
										array('m' => $_m, 'c' => $_c, 'a' => $_a), $_args + $args);
								}
							} elseif (isset($matches['fid'])) {
								$forum = Wekit::load('forum.PwForum')->getForum($matches['fid']);
								$action = array(
									'category' => array('m' => 'bbs', 'c' => 'cate', 'a' => 'run'),
									'forum' => array('m' => 'bbs', 'c' => 'thread', 'a' => 'run'),
									'sub' => array('m' => 'bbs', 'c' => 'thread', 'a' => 'run'),
									'sub2' => array('m' => 'bbs', 'c' => 'thread', 'a' => 'run'),
									);
								$forum_type = isset($forum['type']) ? $forum['type'] : 'forum';
								return array_merge($matches, 
										$action[$forum_type], $args);
							}
						}
						$route = explode('/', $v['route']);
						return array_merge($matches, 
							array('m' => $route[0], 'c' => $route[1], 'a' => $route[2]), $args);
					}
				}
			}
		}
		
		/* 解析普通伪静态 */
		if ($this->rewrite_common && strpos($path, '.php') === false) return $this->_matchCommon(
			$path);
		
		$r = (false !== strpos($path, '?')) ? WindUrlHelper::urlToArgs($path) : array();
		if ($rawDecode) return $r;
		$return = array();
		if (isset($r['m']) || isset($r['c']) || isset($r['a'])) {
			$return['m'] = isset($r['m']) ? $r['m'] : $this->default_m;
			$return['c'] = isset($r['c']) ? $r['c'] : 'index';
			$return['a'] = isset($r['a']) ? $r['a'] : 'run';
		}
		return $return;
	}

	/**
	 * 万事俱备
	 * @param $request WindHttpRequest
	 */
	protected function init($build = false, $request = null) {
		if (!$this->_init) {
			$router = Wind::getComponent('router');
			$this->default_m || $this->default_m = Wind::getApp()->getConfig('default-module', '', $router->getDefaultModule()); 
			if ($this->getConfig('default')) {
				$router->setDefaultModule($this->default_m);
			}
			$rule = $this->_getRule();
			if (isset($rule['default'])) {
				$this->rewrite_common = true;
				unset($rule['default']);
			}
			$rule && $this->rewrite_special = true;
			if ($request) {
				$this->origialBase = $this->base = $request->getBaseUrl();
				$this->scheme = $request->getScheme() . '://';
			}
			$this->_init = true;
		}
		if ($build) {
			$this->omit_mca = $this->onlydomain = false;
			$this->dynamicDomain = $this->dynamic = array();
			$this->dynamicHost = '';
			$this->base === null && $this->base = Wind::getApp()->getRequest()->getBaseUrl();
		}
	}

	/**
	 * 初始化配置
	 *
	 * @return array
	 */
	private function _getRule() {
		static $conf = null;
		if ($conf === null) {
			$conf = Wekit::C()->site->get('rewrite', array());
		}
		return $conf;
	}

	/**
	 * 初始化配置
	 *
	 * @return array
	 */
	private function _getDomainKey() {
		return array(
			'bbs/cate/run' => 'fid', 
			'bbs/thread/run' => 'fid', 
			'bbs/read/run' => 'fid', 
			'special/index/run' => 'id');
	}
	
	private function _getType($type) {
		$all = array(
			'bbs/cate/run' => 'forum',
			'bbs/thread/run' => 'forum',
			'bbs/read/run' => 'forum',
			'special/index/run' => 'special');
		return isset($all[$type]) ? $all[$type] : '';
	}

	/**
	 * 根据类型获取域名
	 *
	 * @return array
	 */
	private function _getDomainByType($type) {
		$domain_type = $this->_getType($type);
		if (!$domain_type) return array(array(), '');
		static $domain = array();
		if (!isset($domain[$domain_type])) {
			$temp = array();
			if (Wekit::C('site', "domain.{$domain_type}.isopen")) {
				$temp = $this->_getDomain('id', 'domain', $domain_type, true);
			}
			$domain[$domain_type] = $temp;
		}
		return array($domain[$domain_type], $domain_type);
	}
	

	/**
	 * 获取域名
	 *
	 * @param string $key
	 * @param string $value 
	 * @param string $type 域名类型 forum, special
	 * @return array  $key => $value 键值对
	 */
	private function _getDomain($key = 'domain', $value = 'domain', $type = 'forum', $absolute = false) {
		static $result = array();
		static $domain = null;
		if (!isset($result[$type][$key][$value])) {
			//不论match，build，只查一次域名表，表中存放的是所有域名，不包括空间。数量大概在几十个
			$domain === null && $domain = Wekit::load('domain.PwDomain')->getAll();
			foreach ($domain as $v) {
				if ($v['domain_type'] == $type) {
					if ($value == 'domain' && $absolute) $v[$value] = $this->scheme . $v[$value] . '.' . $v['root'];
					$temp[$v[$key]] = $v[$value];
				}
			}
			$result[$type][$key][$value] = $temp;
		}
		return $result[$type][$key][$value];
	}

	/**
	 * 初始化配置
	 *
	 * @return array
	 */
	private function _getAppType() {
		static $conf = null;
		if ($conf === null) {
			$conf = Wekit::C()->site->get('domain.app', array());
		}
		return $conf;
	}

	/**
	 * 初始化配置
	 *
	 * @return array
	 */
	private function _getMulti() {
		static $conf = null;
		if ($conf === null) {
			$conf = @include (Wind::getRealPath($this->entrance));
			$conf = $conf ? $conf : array();
		}
		return $conf;
	}

	/**
	 * 获取应用域名
	 *
	 * @param string $_m        	
	 * @return string
	 */
	private function _getModuleDomain($_m) {
		$appType = $this->_getAppType();
		if (isset($appType[$_m])) return $appType[$_m] . $this->base;
		isset($appType['default']) || $this->dynamicHost = Wekit::url()->base;
		return isset($appType['default']) ? $appType['default'] . $this->base : ($this->origialBase != $this->base ? Wekit::url()->base : '');
	}

	/**
	 * 分析参数
	 *
	 * @param AbstractWindRouter $router
	 * @param string $action
	 * @param array $args
	 * @return array 
	 */
	private function _resolveMca($router, $action, $args) {
		list($action, $_args) = explode('?', $action . '?');
		$args = array_merge($args, ($_args ? WindUrlHelper::urlToArgs($_args, false) : array()));
		$action = trim($action, '/');
		$tmp = explode('/', $action . '/');
		end($tmp);
		if (5 === count($tmp) && !strncasecmp('app/', $action, 4)) {
			list($_a, $_c, $_app_name, $_m) = array(prev($tmp), prev($tmp), prev($tmp), prev($tmp));
			$args['app'] = $_app_name;
		} else {
			list($_a, $_c, $_m) = array(prev($tmp), prev($tmp), prev($tmp));
		}
		$_m = $_m ? $_m : $router->getDefaultModule();
		$_c = $_c ? $_c : $router->getDefaultController();
		$_a = $_a ? $_a : $router->getDefaultAction();
		return array($_m, $_c, $_a, $args);
	}

	/**
	 * 生成脚本文件
	 *
	 * @param string $_m        	
	 * @param string $_c        	
	 * @param string $_a        	
	 * @return string
	 */
	private function _buildScript($_m, $_c, $_a, &$args) {
		$pattern = "$_m/$_c/$_a";
		foreach ($this->_getMulti() as $k => $v) {
			if (strpos($v, '*')) {
				$v = str_replace(array('*', '/'), array('\w*', '\/'), $v);
				if (preg_match('/^' . $v . '$/i', $pattern)){
					$args['a'] = $_a;
					$this->omit_mca = true;
					return "/$k";
				}
			} elseif ($v == $pattern) {
				$this->omit_mca = true;
				return "/$k";
			}
		}
		return '/index.php';
	}
	
	/**
	 * 获取待验证有效的url串
	 *
	 * @param string $path
	 * @return string
	 */
	private function _getRawPath($path) {
		$rawpath = $path;
		false !== ($pos = strpos($path, 'index.php')) && $rawpath = substr($path, $pos + 9);
		return trim($rawpath, '/');
	}
	
	/**
	 * 过滤无效url
	 *
	 * @param string $rawpath
	 * @throws WindException
	 */
	private function _filterIllegal($rawpath) {
		foreach (array('#', '?') as $symbol) {
			list($rawpath) = explode($symbol, $rawpath, 2);
		}
		if ($rawpath) {
			throw new WindException('Unable to resolve the request!', 404);
		}
	}
	
	private function _getDefault() {
		return array('m' => $this->default_m, 'c' => 'index', 'a' => 'run');
	}
}

?>