<?php
/**
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com/license.php
 * @version $Id$
 * @package wind
 */
class PwTemplateCompilerThemeUrl extends AbstractWindTemplateCompiler {
	private $_type = '';
	private $_theme = '';
	
	/*
	 * (non-PHPdoc) @see AbstractWindTemplateCompiler::compile()
	 */
	public function compile($key, $content) {
		$content = substr($content, 8, -1);
		if (!$content) return '';
		$themeBaseUrl = 'Wind::getComponent(\'response\')->getData(\'G\', \'url\', \'themes\')';
		preg_match('/(\w*.)?(\w*.)?(css|js|images)(.\w*)?/i', $content, $matchs);
		if (!$matchs) return '';
		if (empty($matchs[3])) return '';
		$pack = $theme = '';
		if (!empty($matchs[1])) {
			$themeType = trim($matchs[1], '.');
			$pack = Wekit::C('site', 'theme.' . $themeType . '.pack');
			$theme = empty($matchs[2]) ? '\'.Wekit::C(\'site\', \'theme.' . $themeType . '.default\').\'' : trim(
				$matchs[2], '.');
		} else {
			list($theme, $pack) = $this->windViewerResolver->getWindView()->getTheme(0);
			$pack && $pack = str_replace('THEMES:', '', $pack);
		}
		$content = $pack ? '.\'/' . str_replace('.', '/', $pack) . '\'' : '';
		$content .= $theme ? '.\'/' . $theme . '\'' : '';
		$content .= '.\'/' . $matchs[3] . '\'';
		
		if ($matchs[3] === 'css') {
			$content .= '.Wekit::getGlobal(\'theme\',\'debug\')';
		}
		$content = '<?php echo ' . $themeBaseUrl . $content . '; ?>';
		$content = str_replace('\'.\'', '', $content);
		return $content;
	}
}

?>