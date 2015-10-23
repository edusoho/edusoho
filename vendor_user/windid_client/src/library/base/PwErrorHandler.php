<?php
/**
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: PwErrorHandler.php 28685 2013-05-20 07:17:32Z gao.wanggao $
 * @package wind
 */
class PwErrorHandler extends WindError {
	
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
		
		//render json
		$type = $this->getRequest()->getAcceptTypes();
		// 如果是含有上传的递交，不能采用ajax的方式递交，需要以html的方式递交，并且返回的结果需要是json格式，将以json=1传递过来标志
		$json = $this->getRequest()->getRequest('_json');
		$requestJson = $this->getRequest()->getIsAjaxRequest() && strpos(strtolower($type), 
			"application/json") !== false;
		if ($requestJson || $json == 1) {
			Wind::$isDebug & 1 && $message = nl2br($message . "\r\n" . $file . ":" . $line . "\r\n");
			$this->getResponse()->setHeader('Content-type', 
				'application/json; charset=' . $this->getResponse()->getCharset());
			echo Pw::jsonEncode(
				array(
					'referer' => null, 
					'refresh' => null, 
					'state' => 'fail', 
					'message' => $message));
			exit();
		}
		
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