<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * pw组件调用机制
 *
 * @author JianMin Chen <sky_hold@163.com> 2011-12-19
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwHook.php 24919 2013-02-26 11:36:12Z jieyin $
 * @package wekit
 * @subpackage engine
 */
class PwHook {
	
	/**
	 * @var WindNormalViewerResolver
	 */
	private static $viewer = null;
	private static $__alias = '';
	private static $methods = array();
	private static $hooks = array();
	private static $prekeys = array();
	private static $prehooks = array();

	/**
	 * 预设查询键值，批量查询缓存(性能优化设置)
	 *
	 * @param array $keys 查询键值 <例：array('c_read_run', 'm_PwThreadDisplay')>
	 * @return void
	 */
	public static function preset($keys) {
		foreach ($keys as $key) {
			self::$prekeys[] = $key;
		}
	}

	/**
	 * 初始化注册列表
	 *
	 * @return void
	 */
	public static function initRegistry() {
		$data = Wekit::load('hook.srv.PwHookInjectService')->fetchInjectByHookName(self::$prekeys);
		foreach ($data as $key => $value) {
			self::$hooks[$key] = $value;
		}
		self::$prekeys = array();
	}

	/**
	 * 获得指定扩展点的全部扩展调用
	 *
	 * @param string $registerKey
	 * @return array
	 */
	public static function getRegistry($registerKey) {
		if (self::$prekeys) self::initRegistry();
		if (!isset(self::$hooks[$registerKey])) {
			self::$hooks[$registerKey] = Wekit::load('hook.srv.PwHookInjectService')->getInjectByHookName($registerKey);
		}
		if (isset(self::$prehooks[$registerKey])) {
			self::$hooks[$registerKey] = array_merge(self::$hooks[$registerKey], self::$prehooks[$registerKey]);
			unset(self::$prehooks[$registerKey]);
		}
		return self::$hooks[$registerKey];
	}

	/**
	 * 手动注册钩子
	 *
	 * @param string $registerKey 钩子名
	 * @param array $inject 注入信息 <array('class' => 'SRV:forum.srv.PwThreadDisplay', 'method' => 'escapeSpace', 'loadway' => '...', ...)>
	 */
	public static function registerHook($registerKey, $inject) {
		self::$prehooks[$registerKey][] = $inject;
	}

	/**
	 * 模板视图hook渲染方法
	 * 
	 * 模板Hook挂在实现{@example<pre>
	 * 按照如下写法实现在模板中的钩子挂在.
	 * <hook class='$a' method='display1' />
	 * 上面的写法将被解析为:
	 * PwHook::display(array($a,'display1'),$args,$viewer);
	 * </pre>}
	 * <note>注意: 是用模板标签方式调用无需显示调用该方法.</note>
	 * @param string|array $callback
	 * @param array $args
	 * @param string $alias
	 * @param WindViewerResolver $viewer
	 */
	public static function display($callback, $args, $alias, $viewer) {
		if (!$callback || !is_array($args)) return;
		self::$viewer = $viewer;
		self::$__alias = $alias;
		call_user_func_array($callback, $args);
	}

	/**
	 * 编译扩展的模板内容并显示
	 * 
	 * @param string $hookname 扩展接口名称
	 * @param string $template 模板名称
	 * @param boolean $optimi 是否将模板缓存到钩子的alias中 
	 * @param array $args 参数列表
	 * @return void
	 */
	public static function template($hookname, $template, $optimi = true) {
		$args = func_get_args();
		unset($args[0], $args[1], $args[2]);
		self::segment($template, array_values($args), $hookname, $optimi ? self::$__alias : '');
	}

	/**
	 * 编译模板片段，并将模板片段放在name缓存文件中
	 * 模板文件中允许存在如下三种形式：
	 * <pre>
	 * 1、第一种：
	 * <hook-action name="hook1" args='a,b,c'>
	 * <div>i am from hook1 {$a} |{$b}|{$c}</div>
	 * </hook-action>
	 * 如上将会被编译成：
	 * function templateName_hook1($a, $b, $c){
	 * }
	 * 2、第二种：
	 * <hook-action name="hook2">
	 * <div> i am from hook2 {$data} </div>
	 * </hook-action>
	 * 如上将会编译成：
	 * function templateName_hook2($data){
	 * }
	 * 3、第三种：
	 * <div> i am from segment {$data}</div>
	 * 如上将会被编译成：
	 * function templateName($data) {
	 * }
	 * 
	 * 模板标签：
	 * <segment alias='' name='' args='' tpl='' />
	 * tpl文件中的模板内容按照如上三种规则被编译之后，将会保存到__segment_alias文件中
	 * 调用方法根据：
	 * tpl_name来调用,如果func没有写，则调用方法为tpl，否则为tpl_func,传入参数为args
	 * </pre>
	 *
	 * @param string $template
	 * @param array $args
	 * @param string $func
	 * @param string $alias
	 * @param WindViewResolve $viewer
	 * @return 
	 */
	public static function segment($template, $args, $func = '', $alias = '', $viewer = null) {
		if ($viewer instanceof WindViewerResolver) self::$viewer = $viewer;
		$_prefix = str_replace(array(':', "."), '_', $template);
		$alias = '__segment_' . strtolower($alias ? $alias : $_prefix);
		list($templateFile, $cacheCompileFile) = self::$viewer->getWindView()->getViewTemplate(
			$template);
		$pathinfo = pathinfo($cacheCompileFile);
		$cacheCompileFile = $pathinfo['dirname'] . '/' . $alias . '.' . $pathinfo['extension'];
		$_method = strtoupper($func ? $_prefix . '_' . $func : $_prefix);
		if (WIND_DEBUG) {
			WindFolder::mkRecur(dirname($cacheCompileFile));
			WindFile::write($cacheCompileFile, '', WindFile::READWRITE);
		} else {
			if (!function_exists($_method) && is_file($cacheCompileFile)) {
				include $cacheCompileFile;
			}
			if (function_exists($_method)) {
				call_user_func_array($_method, $args);
				return;
			}
		}
		
		if (!$content = self::_resolveTemplate($templateFile, strtoupper($_prefix))) return;
		$_content = array();
		foreach ($content as $method => $_item) {
			$_tmpArgs = '';
			foreach ($_item[1] as $_k) {
				$_tmpArgs .= '$' . trim($_k) . ',';
			}
			$windTemplate = Wind::getComponent('template');
			$_content[] = '<?php if (!function_exists("' . $method . '")) {function ' . $method . '(' . trim(
				$_tmpArgs, ',') . '){?>';
			$_content[] = $windTemplate->compileStream($_item[0], self::$viewer);
			$_content[] = '<?php }}?>';
		}
		
		WindFolder::mkRecur(dirname($cacheCompileFile));
		WindFile::write($cacheCompileFile, implode("\r\n", $_content), WindFile::APPEND_WRITE);
		include $cacheCompileFile;
		call_user_func_array($_method, $args);
	}

	/**
	 * 获得指定扩展点的全部扩展调用
	 * 
	 * 过滤当前挂在的所有{@see $filters},根据filter的表达式定义过滤当前需要被执行的所有filter,
	 * 并返回.该方法在{@see PwBaseController::runHook}中被使用.当前表达式参数解析支持,
	 * <i>request,service(PwBaseHookService)</i>{@example <code>
	 * 例如: 'expression' => 'special.get==1'
	 * 则该该方法会去判断request,get请求中的special的值是否等于1,
	 * 如果为true则注册该过滤器,如果为false则不注册该过滤器,当expression不定义时,
	 * 则认为在任何条件下都注册该过滤器.
	 * </code>}
	 * @param array $filters
	 * @param PwBaseHookService $service
	 * @return array
	 */
	public static function resolveActionHook($filters, $service = null) {
		$_filters = array();
		foreach ((array) $filters as $filter) {
			if (empty($filter['class'])) continue;
			if (!is_file(Wind::getRealPath($filter['class']))) continue;
			if (!empty($filter['expression'])) {
				$v1 = '';
				list($n, $p, $o, $v2) = WindUtility::resolveExpression($filter['expression']);
				switch (strtolower($n)) {
					case 'service':
						$call = array($service, 'getAttribute');
						break;
					case 'config':
						$call = array(self, '_getConfig');
						break;
					case 'global':
						$call = array('Wekit', 'getGlobal');
						break;
					default:
						$call = array(self, '_getRequest');
						break;
				}
				$v1 = call_user_func_array($call, explode('.', $p));
				if (!WindUtility::evalExpression($v1, $v2, $o)) continue;
			}
			$_filters[] = $filter;
		}
		return $_filters;
	}

	/**
	 * @param string $key
	 * @param string $method get/post
	 * @return mixed
	 */
	private static function _getRequest($key, $method = 'get') {
		if (!$key) return '';
		switch (strtolower($method)) {
			case 'get':
				return Wind::getApp()->getRequest()->getGet($key);
			case 'post':
				return Wind::getApp()->getRequest()->getPost($key);
			default:
				return Wind::getApp()->getRequest()->getRequest($key);
		}
	}

	/**
	 * 获取配置条件约束
	 */
	private static function _getConfig($var) {
		if (func_num_args() > 1) {
			$args = array_slice(func_get_args(), 1);
			return Wekit::C($var, implode('.', $args));
		}
		return '';
	}

	/**
	 * 解析模板内容并返回
	 * 
	 * 将输入的模板内容解析为方法数组{@example <pre>
	 * 以下模板内容将解析为:
	 * <hook-action name="testHook" args='a,c'>
	 * <div>
	 * hi, i am testHook
	 * </div>
	 * </hook-action>
	 * <hook-action name="testHook1">
	 * <div>
	 * hi, i am testHook
	 * </div>
	 * </hook-action>
	 * 
	 * $content = array(
	 * 'testHook' => array('content', array('a','c')),
	 * 'testHook1' => array('content', array('data'))
	 * );
	 * </pre>}
	 * @param string $template
	 * @return array
	 */
	private static function _resolveTemplate($template, $_prefix) {
		if (false === ($content = WindFile::read($template))) throw new PwException(
			'template.path.fail', 
			array('{parm1}' => 'wekit.engine.hook.PwHook._resolveTemplate', '{parm2}' => $template));
		
		self::$methods = array();
		$content = preg_replace_callback('/<(\/)?hook-action[=,\w\s\'\"]*>(\n)*/i', array(self, '_pregContent'), $content);
		$content = explode("</hook-action>", $content);
		$_content = array();
		$_i = 0;
		//如果该模板中只有一段片段没有使用hook-action，则该方法名将会设为该模板名称，接受的参数为$data
		if (count(self::$methods) == 0) {
			$_content[$_prefix] = array($content[0], array('data'));
		} else {
			$_i = 0;
			foreach (self::$methods as $method) {
				$key = $method['name'] ? $_prefix . '_' . strtoupper($method['name']) : $_prefix . '_' . ($_i + 1);
				$args = $method['args'] ? explode(',', $method['args']) : array('data');
				$_content[$key] = array($content[$_i], $args);
				$_i++;
			}
		}
		return $_content;
	}

	/**
	 * 解析hook-action标签中的属性
	 * 
	 * 该标签支持两个属性，分别是：
	 * <ul>
	 * <li>name: 定义出来的该片段的function名字</li>
	 * <li>args: 定义出该片段中接受的参数，缺省的情况下将会使用data作为参数</li>
	 * </ul>
	 * PwHook::$methods中每一个元素都含有name和args两个子元素
	 * 
	 * @param string $content
	 * @return string
	 */
	private static function _pregContent($content) {
		if (isset($content[1]) && $content[1] == '/') return "</hook-action>";
		preg_match('/(?<=name=([\'\"]))(.*?)(?=\1)/ie', $content[0], $match1);
		preg_match('/(?<=args=([\'\"]))(.*?)(?=\1)/ie', $content[0], $match2);
		self::$methods[] = array('name' => $match1[0], 'args' => $match2[0]);
		return '';
	}
}
?>