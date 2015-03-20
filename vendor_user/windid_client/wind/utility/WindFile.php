<?php
Wind::import("WIND:utility.WindString");
/**
 * 文件工具类
 *
 * @author Qian Su <aoxue.1988.su.qian@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindFile.php 3298 2012-01-06 12:48:26Z yishuo $
 * @package utility
 */
class WindFile {
	/**
	 * 以读的方式打开文件，具有较强的平台移植性
	 * 
	 * @var string 
	 */
	const READ = 'rb';
	/**
	 * 以读写的方式打开文件，具有较强的平台移植性
	 * 
	 * @var string 
	 */
	const READWRITE = 'rb+';
	/**
	 * 以写的方式打开文件，具有较强的平台移植性
	 * 
	 * @var string 
	 */
	const WRITE = 'wb';
	/**
	 * 以读写的方式打开文件，具有较强的平台移植性
	 * 
	 * @var string 
	 */
	const WRITEREAD = 'wb+';
	/**
	 * 以追加写入方式打开文件，具有较强的平台移植性
	 * 
	 * @var string 
	 */
	const APPEND_WRITE = 'ab';
	/**
	 * 以追加读写入方式打开文件，具有较强的平台移植性
	 * 
	 * @var string 
	 */
	const APPEND_WRITEREAD = 'ab+';
	
	/**
	 * 删除文件
	 * 
	 * @param string $filename 文件名称
	 * @return boolean
	 */
	public static function del($filename) {
		return @unlink($filename);
	}

	/**
	 * 保存文件
	 * 
	 * @param string $fileName          保存的文件名
	 * @param mixed $data               保存的数据
	 * @param boolean $isBuildReturn    是否组装保存的数据是return $params的格式，如果没有则以变量声明的方式保存,默认为true则以return的方式保存
	 * @param string $method            打开文件方式，默认为rb+的形式
	 * @param boolean $ifLock           是否对文件加锁，默认为true即加锁
	 */
	public static function savePhpData($fileName, $data, $isBuildReturn = true, $method = self::READWRITE, $ifLock = true) {
		$temp = "<?php\r\n ";
		if (!$isBuildReturn && is_array($data)) {
			foreach ($data as $key => $value) {
				if (!preg_match('/^\w+$/', $key)) continue;
				$temp .= "\$" . $key . " = " . WindString::varToString($value) . ";\r\n";
			}
			$temp .= "\r\n?>";
		} else {
			($isBuildReturn) && $temp .= " return ";
			$temp .= WindString::varToString($data) . ";\r\n?>";
		}
		return self::write($fileName, $temp, $method, $ifLock);
	}

	/**
	 * 写文件
	 *
	 * @param string $fileName 文件绝对路径
	 * @param string $data 数据
	 * @param string $method 读写模式,默认模式为rb+
	 * @param bool $ifLock 是否锁文件，默认为true即加锁
	 * @param bool $ifCheckPath 是否检查文件名中的“..”，默认为true即检查
	 * @param bool $ifChmod 是否将文件属性改为可读写,默认为true
	 * @return int 返回写入的字节数
	 */
	public static function write($fileName, $data, $method = self::READWRITE, $ifLock = true, $ifCheckPath = true, $ifChmod = true) {
		touch($fileName);
		if (!$handle = fopen($fileName, $method)) return false;
		$ifLock && flock($handle, LOCK_EX);
		$writeCheck = fwrite($handle, $data);
		$method == self::READWRITE && ftruncate($handle, strlen($data));
		fclose($handle);
		$ifChmod && chmod($fileName, 0777);
		return $writeCheck;
	}

	/**
	 * 读取文件
	 *
	 * @param string $fileName 文件绝对路径
	 * @param string $method 读取模式默认模式为rb
	 * @return string 从文件中读取的数据
	 */
	public static function read($fileName, $method = self::READ) {
		$data = '';
		if (!$handle = fopen($fileName, $method)) return false;
		while (!feof($handle))
			$data .= fgets($handle, 4096);
		fclose($handle);
		return $data;
	}

	/**
	 * @param string $fileName
	 * @return boolean
	 */
	public static function isFile($fileName) {
		return $fileName ? is_file($fileName) : false;
	}

	/**
	 * 取得文件信息
	 * 
	 * @param string $fileName 文件名字
	 * @return array 文件信息
	 */
	public static function getInfo($fileName) {
		return self::isFile($fileName) ? stat($fileName) : array();
	}

	/**
	 * 取得文件后缀
	 * 
	 * @param string $filename 文件名称
	 * @return string
	 */
	public static function getSuffix($filename) {
		if (false === ($rpos = strrpos($filename, '.'))) return '';
		return substr($filename, $rpos + 1);
	}
}