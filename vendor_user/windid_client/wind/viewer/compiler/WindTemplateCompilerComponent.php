<?php
Wind::import('WIND:viewer.AbstractWindTemplateCompiler');
/**
 * 组件标签编译器
 * 
 * 标签使用例子:
 * <code>
 * <component name="test" args="my:index:run" templateDir="template" 
 * appConfig="WIND:config.config.php" componentPath="WIND:demos.helloworld"/></code>
 * @author xiaoxiao <x_824@sina.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindTemplateCompilerComponent.php 3904 2013-01-08 07:01:26Z yishuo $
 * @package viewer
 * @subpackage compiler
 */
class WindTemplateCompilerComponent extends AbstractWindTemplateCompiler {
	protected $name = ''; //组件名字
	protected $args = ''; //传递给组件的参数
	protected $templateDir = ''; //组件调用的模板路径
	protected $appConfig = ''; //组件的配置文件
	protected $appConfigSuffix = 'php'; //配置文件的缺省格式
	protected $componentPath = ''; //组件的入口地址

	
	/* (non-PHPdoc)
	 * @see AbstractWindTemplateCompiler::compile()
	 */
	public function compile($key, $content) {
		return $this->getScript($content);
	}

	/**
	 * @return string
	 */
	private function getScript($content) {
		$params = $this->matchConfig($content);
		if (!isset($params['name']) || !isset($params['componentPath'])) throw new WindException('[viewer.compiler.WindTemplateCompilerComponent.getScript] component config error!');
		$content = "<?php\r\n" . $this->rebuildConfig($params) . (isset($params['args']) ? $this->registerUrlParams($params) : '') . "\$componentPath = Wind::getRealDir('" . $params['componentPath'] . "');\r\n" . "Wind::register(\$componentPath, '" . $params['name'] . "');\r\n" . "Wind::run('" . $params['name'] . "', \$config);\r\n?>";
		return $content;
	}

	/**
	 * 编译获得配置文件
	 * 
	 * @param array $params
	 * @return array
	 */
	private function rebuildConfig($params) {
		$temp = "\$configParser = new WindConfigParser();\r\n" . "\$configPath = Wind::getRealPath('" . $params['appConfig'] . "', '" . $params['suffix'] . "');\r\n" . "\$config = \$configParser->parse(\$configPath);\r\n" . "\$config = \$config['" . $params['name'] . "'];\r\n";
		if (!isset($params['templateDir'])) return $temp;
		if (isset($params['args']['m']))
			$temp .= "\$config['modules']['" . $params['args']['m'] . "']['view']['config']['template-dir'] = '" . $params['templateDir'] . "';\r\n";
		else {
			$temp .= "foreach(\$config['modules'] as \$key => \$value) {\r\n" . "\t\$config['modules'][\$key]['view']['config']['template-dir'] = '" . $params['templateDir'] . "';\r\n" . "}\r\n";
		}
		return $temp;
	}

	/**
	 * 注册变量信息
	 * 
	 * @param array $params 
	 */
	private function registerUrlParams($params) {
		$temp = "\$routerConfig = \$config['router']['config'];\r\n" . "\$mKey = isset(\$routerConfig['module']['url-param']) ? \$routerConfig['module']['url-param'] : 'm';\r\n" . "\$cKey = isset(\$routerConfig['controller']['url-param']) ? \$routerConfig['controller']['url-param'] : 'c';\r\n" . "\$aKey = isset(\$routerConfig['action']['url-param']) ? \$routerConfig['action']['url-param'] : 'a';\r\n" . "\$_GET[\$mKey] = '" . $params['args']['m'] . "';\r\n" . "\$_GET[\$cKey] = '" . $params['args']['c'] . "';\r\n" . "\$_GET[\$aKey] = '" . $params['args']['a'] . "';\r\n";
		unset($params['args']['a'], $params['args']['c'], $params['args']['m']);
		foreach ($params['args'] as $key => $value) {
			$temp .= "\$_GET['" . $key . "'] = " . (is_array($value) ? $value : "'" . $value . "'") . ";\r\n";
		}
		return $temp;
	}

	/**
	 * 匹配配置信息
	 * 
	 * @param string $content
	 * @return array
	 */
	private function matchConfig($content) {
		preg_match_all('/(\w+=[\'|"]?[\w|.|:]+[\'|"]?)/', $content, $mathcs);
		list($config, $key, $val) = array(array(), '', '');
		foreach ($mathcs[0] as $value) {
			list($key, $val) = explode('=', $value);
			if (!in_array($key, $this->getProperties()) || !$val) continue;
			switch ($key) {
				case 'args':
					$config['args'] = $this->compileArgs(trim($val, '\'"'));
					break;
				default:
					$config[$key] = trim($val, '\'"');
					break;
			}
		}
		return $config;
	}

	/**
	 * 解析传递给url的参数信息
	 * 
	 * @param string $arg
	 * @return array
	 */
	private function compileArgs($arg) {
		$args = explode(':', $arg);
		$urlParams = array();
		list($urlParams['a'], $urlParams['c'], $urlParams['m']) = array('', '', '');
		switch (count($args)) {
			case 1:
				$urlParams['a'] = $args[0];
				break;
			case 2:
				list($urlParams['c'], $urlParams['a']) = $args;
				break;
			case 3:
				list($urlParams['m'], $urlParams['c'], $urlParams['a']) = $args;
				break;
			default:
				break;
		}
		return $urlParams;
	}

	/* (non-PHPdoc)
	 * @see AbstractWindTemplateCompiler::getProperties()
	 */
	protected function getProperties() {
		return array('name', 'templateDir', 'appConfig', 'args', 'componentPath', 'suffix');
	}
}

?>