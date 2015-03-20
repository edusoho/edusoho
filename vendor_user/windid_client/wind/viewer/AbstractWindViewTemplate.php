<?php
Wind::import('WIND:utility.WindFile');
/**
 * 模板编译抽象类定义
 * 
 * 模板编译类的抽象类定义,职责:编译给定模板文件,并返回编译后的内容.
 * 该类只有一个抽象接口需要被实现<i>doCompile</i>,模板编译类只有一个对外的公开接口<i>compile</i>.
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: AbstractWindViewTemplate.php 3904 2013-01-08 07:01:26Z yishuo $
 * @package viewer
 */
abstract class AbstractWindViewTemplate extends WindModule {

	/**
	 * 模板内容编译实现,返回编译后模板内容内容
	 * 
	 * @param string $content 模板内容
	 * @param WindViewerResolver $windViewerResolver
	 * @return string
	 */
	abstract protected function doCompile($content, $windViewerResolver = null);

	/**
	 * 进行视图渲染
	 * 
	 * @param string $templateFile | 模板文件
	 * @param WindViewerResolver $windViewerResolver
	 */
	public function compile($templateFile, $windViewerResolver) {
		$_output = $this->getTemplateFileContent($templateFile);
		$_output = $this->compileDelimiter($_output);
		$_output = $this->doCompile($_output, $windViewerResolver);
		return $_output;
	}

	/**
	 * 渲染流
	 *
	 * @param string $stream
	 * @param WindViewerResolver $windViewerResolver
	 */
	public function compileStream($stream, $windViewerResolver) {
		$_output = $this->compileDelimiter($stream);
		$_output = $this->doCompile($_output, $windViewerResolver);
		return $_output;
	}

	/**
	 * @param string content
	 * @return string
	 */
	protected function compileDelimiter($content) {
		$content = preg_replace('/<!--[{#]/i', "<?php ", $content);
		$content = preg_replace('/[#}]-->/i', "?>", $content);
		
		/*$content = str_replace(array('<!--{', '<!--#'), '<?php ', $content);
		$content = str_replace(array('}-->', '#-->'), '?>', $content);*/
		return $content;
	}

	/**
	 * 获得模板文件内容，目前只支持本地文件获取
	 * 
	 * @param string $templateFile
	 * @return string
	 */
	private function getTemplateFileContent($templateFile) {
		if (false === ($content = WindFile::read($templateFile))) {
			throw new WindViewException('[viewer.AbstractWindViewTemplate.getTemplateFileContent] Unable to open the template file \'' . $templateFile . '\'.');
		}
		return $content;
	}
}
?>