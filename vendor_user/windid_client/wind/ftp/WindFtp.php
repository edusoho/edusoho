<?php
Wind::import('WIND:ftp.AbstractWindFtp');
/**
 * 使用ftp函数实现ftp相关操作
 * 
 * 使用方法和普通类库一样:
 * <code>
 * Wind::import('WIND:ftp.WindFtp');
 * $ftp = new WindFtp(array('server' => '192.168.1.10', 'port' => '21', ‘user' => 'test', 'pwd' => '123456'));
 * print_r($ftp->fileList());
 * </code>
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindFtp.php 3904 2013-01-08 07:01:26Z yishuo $
 * @package ftp
 */
class WindFtp extends AbstractWindFtp {
	
	/**
	 * 被动模式是否开启默认为true开启
	 * 
	 * @var boolean
	 */
	private $isPasv = true;

	/**
	 * 构造函数
	 * 
	 * 通过传入config构造链接对象
	 * 
	 * @param array $config ftp配置文件
	 */
	public function __construct($config = array()) {
		$this->connection($config);
	}

	/**
	 * 链接ftp
	 * 
	 * @param array $config ftp的配置信息：
	 * <ul>
	 * <li>server: ftp主机地址</li>
	 * <li>port: ftp链接端口号，默认为21</li>
	 * <li>user: ftp链接用户名</li>
	 * <li>pwd: ftp链接用户密码</li>
	 * <li>dir: ftp链接后切换的目录,默认为空</li>
	 * <li>timeout: ftp链接超时时间,默认为10秒</li>
	 * <li>ispasv: ftp是否采用被动模式，默认为1，如果配置为0则表示不开启被动模式，其他值都将设置为开启被动模式</li>
	 * </ul>
	 * @return boolean 
	 */
	private function connection($config = array()) {
		$this->initConfig($config);
		if (false === ($this->conn = ftp_connect($this->server, $this->port, $this->timeout))) {
			throw new WindFtpException("[ftp.WindFtp.connection] $this->server:$this->port", 
				WindFtpException::CONNECT_FAILED);
		}
		if (false == ftp_login($this->conn, $this->user, $this->pwd)) {
			throw new WindFtpException('[ftp.WindFtp.connection] ' . $this->user, WindFtpException::LOGIN_FAILED);
		}
		if ($this->isPasv) {
			ftp_pasv($this->conn, true);
		}
		$this->initRootPath();
		return true;
	}

	/**
	 * 获得ftp链接
	 * 
	 * @return resource
	 */
	private function getFtp() {
		if (is_resource($this->conn)) return $this->conn;
		$this->connection();
		return $this->conn;
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindFtp::rename()
	 */
	public function rename($oldName, $newName) {
		return ftp_rename($this->getFtp(), $oldName, $newName);
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindFtp::delete()
	 */
	public function delete($filename) {
		return ftp_delete($this->getFtp(), $filename);
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindFtp::upload()
	 */
	public function upload($sourceFile, $desFile, $mode = 'I') {
		$mode = $this->getMode($sourceFile, $mode);
		if (!in_array(($savedir = dirname($desFile)), array('.', '/'))) {
			$this->mkdirs($savedir);
		}
		$desFile = $this->rootPath . WindSecurity::escapePath($desFile);
		$result = ftp_put($this->getFtp(), $desFile, $sourceFile, $mode);
		if (false === $result) throw new WindFtpException('[ftp.WindFtp.upload] PUT', WindFtpException::COMMAND_FAILED);
		$this->chmod($desFile, 0644);
		return $this->size($desFile);
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindFtp::download()
	 */
	public function download($filename, $localname = '', $mode = 'I') {
		$mode = $this->getMode($filename, $mode);
		return ftp_get($this->getFtp(), $localname, $filename, $mode);
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindFtp::fileList()
	 */
	public function fileList($dir = '') {
		return ftp_nlist($this->getFtp(), $dir);
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindFtp::close()
	 */
	public function close() {
		is_resource($this->conn) && ftp_close($this->conn);
		$this->conn = null;
		return true;
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindFtp::initConfig()
	 */
	public function initConfig($config) {
		if (!$config || !is_array($config)) return false;
		parent::initConfig($config);
		$this->isPasv = (isset($config['ispasv']) && $config['ispasv'] == 0) ? false : true;
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindFtp::mkdir()
	 */
	public function mkdir($dir, $permissions = 0777) {
		$result = ftp_mkdir($this->getFtp(), $dir);
		if (!$result) return false;
		return $this->chmod($result, $permissions) === false ? false : true;
	}

	/**
	 * 给文件赋指定权限
	 * 
	 * @param string $file 待处理的文件
	 * @param int $permissions 文件的需要的权限
	 * @return boolean 设置成功返回true,设置失败返回false
	 */
	private function chmod($file, $permissions = 0777) {
		return ftp_chmod($this->getFtp(), $permissions, $file);
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindFtp::pwd()
	 */
	protected function pwd() {
		return ftp_pwd($this->getFtp()) . '/';
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindFtp::changeDir()
	 */
	public function changeDir($dir) {
		if (false === ftp_chdir($this->getFtp(), $dir)) {
			throw new WindFtpException('[ftp.WindFtp.changeDir] ' . $dir, WindFtpException::COMMAND_FAILED_CWD);
		}
		return true;
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindFtp::size()
	 */
	public function size($file) {
		return ftp_size($this->getFtp(), $file);
	}

	/**
	 * 根据文件获得文件访问的模式
	 * 
	 * @param string $filename 文件名
	 * @param string $mode 模式，二进制还是ASCII上传，I为二进制模式，A为ASCII模式，默认为A模式，如果是auto将会根据文件后缀来设置模式
	 * @return string 返回模式方式FTP_ASCII或是FTP_BINARY
	 */
	private function getMode($filename, $mode) {
		if (strcasecmp($mode, 'auto') == 0) {
			$ext = WindFile::getSuffix($filename);
			$mode = (in_array(strtolower($ext), 
				array(
					'txt', 
					'text', 
					'php', 
					'phps', 
					'php4', 
					'js', 
					'css', 
					'htm', 
					'html', 
					'phtml', 
					'shtml', 
					'log', 
					'xml'))) ? 'A' : 'I';
		}
		return ($mode == 'A') ? FTP_ASCII : FTP_BINARY;
	}
}