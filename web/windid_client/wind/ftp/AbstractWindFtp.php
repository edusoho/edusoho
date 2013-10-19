<?php
Wind::import('WIND:ftp.exception.WindFtpException');
/**
 * FTP基类
 * 
 * 定义了FTP类拥有的接口
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: AbstractWindFtp.php 3930 2013-02-05 03:55:24Z yishuo $
 * @package ftp
 */
abstract class AbstractWindFtp {
	
	/**
	 * ftp主机地址
	 *
	 * @var string
	 */
	protected $server = '';
	
	/**
	 * ftp链接端口号
	 *
	 * @var int
	 */
	protected $port = 21;
	
	/**
	 * ftp链接的用户名
	 *
	 * @var string	 
	 */
	protected $user = '';
	
	/**
	 * ftp链接的用户密码
	 *
	 * @var string
	 */
	protected $pwd = '';
	
	/**
	 * ftp链接之后使用的当前路径
	 *
	 * @var string
	 */
	protected $dir = '';
	
	/**
	 * ftp链接的过期时间单位秒
	 * 
	 * @var int
	 */
	protected $timeout = 10;
	
	/**
	 * 保存ftp的跟目录路径
	 *
	 * @var string
	 */
	protected $rootPath = '';
	
	/**
	 * ftp链接对象
	 *
	 * @var resource
	 */
	protected $conn = null;

	/**
	 * 初始化配置信息
	 * 
	 * @param array $config ftp的配置信息：
	 * <ul>
	 * <li>server: ftp主机地址</li>
	 * <li>port: ftp链接端口号，默认为21</li>
	 * <li>user: ftp链接用户名</li>
	 * <li>pwd: ftp链接用户密码</li>
	 * <li>dir: ftp链接后切换的目录,默认为空</li>
	 * <li>timeout: ftp链接超时时间,默认为10秒</li>
	 * </ul>
	 * @return boolean 配置成功返回true,配置失败返回false
	 */
	public function initConfig($config) {
		if (!$config || !is_array($config)) return false;
		isset($config['server']) && $this->server = $config['server'];
		isset($config['port']) && $this->port = $config['port'];
		isset($config['user']) && $this->user = $config['user'];
		isset($config['pwd']) && $this->pwd = $config['pwd'];
		isset($config['dir']) && $this->dir = $config['dir'];
		isset($config['timeout']) && $this->timeout = $config['timeout'];
		return true;
	}

	/**
	 * 重命名文件
	 * 
	 * @param string $oldName 现在的文件名
	 * @param string $newName 新的文件名
	 * @return boolean 重命名成功则返回true,失败则返回false
	 */
	abstract public function rename($oldName, $newName);

	/**
	 * 删除文件
	 * 
	 * @param string $filename 待删除的文件
	 * @return boolean 删除成功返回true,删除失败返回false
	 */
	abstract public function delete($filename);

	/**
	 * 上传文件
	 * 
	 * 'I' == BINARY mode
     * 'A' == ASCII mode
	 * 
	 * @param string $sourceFile 待上传的文件
	 * @param string $desFile 文件上传的存放位置
	 * @param string $mode 上传模式二进制还是ASCII上传，I为二进制模式，A为ASCII模式，默认为A模式
	 * @return int 返回上传文件的大小 
	 */
	abstract public function upload($sourceFile, $desFile, $mode = 'I');

	/**
	 * 下载文件
	 * 
	 * @param string $localfile  下载文件存放位置
	 * @param string $remotefile 待下载的文件
	 * @param string $mode 下载的模式二进制还是ASCII上传，I为二进制模式，A为ASCII模式，默认为A模式
	 * @return boolean 返回文件下载是否成功
	 */
	abstract public function download($localfile, $remotefile = '', $mode = 'I');

	/**
	 * 列出给定目录的文件列表
	 * 
	 * @param string $dir 目录,默认为空即为当前目录
	 * @return array 返回该目录下的文件列表
	 */
	abstract public function fileList($dir = '');

	/**
	 * 关闭ftp链接
	 * 
	 * @return boolean 返回关闭链接是否成功
	 */
	abstract public function close();

	/**
	 * 创建文件夹
	 * 
	 * @param string $dir 待创建的文件夹
	 * @return boolean 创建文件夹成功则返回true,失败则返回false
	 */
	abstract public function mkdir($dir);

	/**
	 * 更改当前目录到指定目录下
	 * 
	 * @param string $dir 需要设置为当前目录的目录
	 * @return boolean 设置成功则返回true,失败则返回false
	 */
	abstract public function changeDir($dir);

	/**
	 * 获得文件大小
	 * 
	 * @param string $file 待获取的文件
	 * @return int 获取成功返回文件大小
	 */
	abstract public function size($file);

	/**
	 * 获得当前路径
	 * 
	 * @return string 返回当前路径
	 */
	abstract protected function pwd();

	/**
	 * 级联创建文件夹
	 * 
	 * @param string $dir 待创建文件夹路径
	 * @param string $permissions 创建的文件夹的权限
	 * @return boolean 创建成功返回true创建失败返回false
	 */
	public function mkdirs($dir, $permissions = 0777) {
		$dir = explode('/', WindSecurity::escapePath($dir));
		$dirs = '';
		$result = false;
		$count = count($dir);
		for ($i = 0; $i < $count; $i++) {
			if ('.' !== $dir[$i] && '..' !== $dir[$i]) {
				$result = $this->mkdir($dir[$i], $permissions);
				$this->changeDir($this->rootPath . $dirs . $dir[$i]);
				$dirs .= "$dir[$i]/";
			}
		}
		$this->changeDir($this->rootPath);
		return $result;
	}

	/**
	 * 检查文件是否存在
	 * 
	 * @param string $filename 待检查的文件
	 * @return boolean 文件存在则返回true,失败则返回false
	 */
	public function file_exists($filename) {
		$directory = substr($filename, 0, strrpos($filename, '/'));
		$filename = str_replace("$directory/", '', $filename);
		if ($directory) {
			$directory = $this->rootPath . $directory . '/';
		} else {
			$directory = $this->rootPath;
		}
		$this->changeDir($directory);
		$list = $this->fileList();
		$this->changeDir($this->rootPath);
		if (!empty($list) && in_array($filename, $list)) return true;
		return false;
	}

	/**
	 * 重设当前目录为初始化目录信息
	 */
	protected function initRootPath() {
		$this->rootPath = $this->pwd();
		if ($this->dir) {
			$this->rootPath .= trim(str_replace('\\', '/', $this->dir), '/') . '/';
		}
		$this->changeDir($this->rootPath);
	}
}