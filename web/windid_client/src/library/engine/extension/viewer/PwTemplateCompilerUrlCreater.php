<?php
/**
 * ((\$[a-zA-Z]+->)|([a-zA-Z]+::)|\$)([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff\[\]"]*)
 *
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com/license.php
 * @version $Id$
 * @package wind
 */
class PwTemplateCompilerUrlCreater extends AbstractWindTemplateCompiler {
	private $_variables = array();
	
	/*
	 * (non-PHPdoc) @see AbstractWindTemplateCompiler::compile()
	 */
	public function compile($key, $content) {
		$content = substr($content, 6, -1);
		list($content, $route) = explode('|', $content . '|');
		
		$this->_variables = array();
		$content = preg_replace_callback(
			'/((\$[a-zA-Z_]+->)|([a-zA-Z_]+::)|\$)([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\[[a-zA-Z0-9_\x7f-\xff\'\"\$\[\]]+\])*)/i', 
			array($this, '_variable'), trim($content));
		
		$content = WindUrlHelper::createUrl($content, array(), '', $route, false);
		$content = $this->_compile(ltrim($content, '/'));
		
		//变量过滤，添加rawurlencode 安全过滤，如果是链接好的串需要使用者控制
		foreach ($this->_variables as $key => $var) {
			$content = str_replace($key, '\', rawurlencode(' . $var . '),\'', $content);
		}

		$content = str_replace(",''", '', '\'' . $content . '\'');
		
		/* foreach ((array) Wekit::getGlobal('_url_') as $key => $value) {
			$this->_variables[$key] = $value;
			$content .= ',\'&' . $key . '=\',Wekit::getGlobal(\'_url_\', ' . $key . ')';
		} */

		$content = $this->_checkUrl($content);
		return '<?php echo ' . $content . '; ?>';
	}

	/**
	 * @param array $matches
	 * @return string
	 */
	private function _variable($matches) {
		$key = WindUtility::generateRandStr(10);
		$this->_variables[$key] = $matches[0];
		return $key;
	}
	
	/**
	 * 分析其中的脚本
	 *
	 * @param string $content
	 * @return string
	 */
	private function _compile($content) {
		if ($route = Wind::getComponent('router')->getRoute('pw')) {
			if ($route->dynamicDomain) {
				if (3 == count($route->dynamicDomain)) {
					$content = $route->dynamicDomain[0] . $route->dynamicDomain[1] . $content . $route->dynamicDomain[2];
				} else {
					$content = $route->dynamicDomain[0] . $route->dynamicDomain[1] . $route->dynamicDomain[4] . $route->dynamicDomain[2] . $content . $route->dynamicDomain[3];
				}
				$content = preg_replace_callback('/<\?php(.*?)\?>/is', array($this, '_parse'), $content);
			} else if ($route->dynamic) {
				if (false !== strpos($content, '{fname}')) {
					$temp = explode('{fname}', $content , 2);
					foreach ($this->_variables as $key => $var) {
						$temp[0] = str_replace($key, '\',' . $var . ',\'', $temp[0]);
						$temp[1] = str_replace($key, '\',' . $var . ',\'', $temp[1]);
					}
					$content = $route->dynamic[0] . 'echo \'' . $temp[0] . '\',' . $route->dynamic[1] . ',\'' . $temp[1] . '\';?>';
				}
				$content = preg_replace_callback('/<\?php(.*?)\?>/is', array($this, '_parse'), $content);
			}
			if ($route->dynamicHost) {
				$content = ltrim(str_replace($route->dynamicHost, '', $content), '/');
			}
			$route->dynamicDomain = $route->dynamic = $route->dynamicDomain = null;
		} 
		return $content;
	}
	
	/**
	 * 编译其中的php语句
	 *
	 * @param array $matches
	 * @return string
	 */
	private function _parse($matches) {
		$str = '\'; '.$matches[1].' echo \'';
		foreach ($this->_variables as $key => $var) {
			$str = str_replace($key, $var, $str);
		}
		return $str;
	}
	
	private function _checkUrl($content) {
		if (strpos($content, '://') === false) {
			$content = 'Wind::getComponent(\'response\')->getData(\'G\', \'url\', \'base\'),\'/\',' . $content;
		}
		return $content;
	}
}

?>