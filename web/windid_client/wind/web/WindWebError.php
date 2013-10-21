<?php
Wind::import('WIND:base.WindError');
/**
 * web mvc 错误句柄处理
 * errorDir错误页面所在目录，在web模式下默认的错误目录为‘WIND:web.view’，{@see
 * AbstractWindFrontController}configTemplate默认值，也可以通过配置当前应用的错误目录相改变这个默认配置。
 * 
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package wind.web
 */
class WindWebError extends WindError {
	
	/* (non-PHPdoc)
	 * @see WindError::showErrorMessage()
	 */
	protected function showErrorMessage($message, $file, $line, $trace, $errorcode) {
		list($fileLines, $trace) = $this->crash($file, $line, $trace);
		
		if (Wind::$isDebug & 2) {
			$log = $message . "\r\n" . $file . ":" . $line . "\r\n";
			foreach ($trace as $key => $value) {
				$log .= $value . "\r\n";
			}
			Wind::getComponent('windLogger')->error($log, 'error', true);
		}
		
		$message = nl2br($message);
		$errDir = Wind::getRealPath($this->errorDir, false);
		if ($this->isClosed)
			$errPage = 'close';
		elseif (is_file($errDir . '/' . $errorcode . '.htm'))
			$errPage = $errorcode;
		else
			$errPage = 'error';
		
		$title = $this->getResponse()->codeMap($errorcode);
		$title = $title ? $errorcode . ' ' . $title : 'unknowen error';
		$__vars['title'] = ucwords($title);
		$__vars['message'] = $message;
		
		if (Wind::$isDebug & 1) {
			$__vars['debug']['file'] = $file;
			$__vars['debug']['line'] = $line;
			$__vars['debug']['trace'] = $trace;
			$__vars['debug']['fileLines'] = $fileLines;
		}
		$this->render($__vars, $errorcode, $errDir, $errPage);
	}

	/**
	 * 错误视图渲染
	 * 
	 * @param array $__vars
	 */
	private function render($__vars, $errorcode, $errDir, $errPage) {
		@extract($__vars, EXTR_REFS);
		unset($__vars);
		ob_start();
		$this->getResponse()->setStatus($errorcode);
		$this->getResponse()->sendHeaders();
		require $errDir . '/' . $errPage . '.htm';
		exit();
	}
}

?>