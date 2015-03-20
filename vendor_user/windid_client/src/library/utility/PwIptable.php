<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * IP地址查询
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class PwIptable {
	private $_from;
	private $_dataFile;
	
	/**
	 * @param $dirName
	 * @return unknown_type
	 */
	public function __construct($dirName = null) {
		$this->_dirName = $dirName ? $dirName : Wind::getRealDir('REP:ipdata');
		$this->_dataFile = $this->_dirName . '/ipindex.dat';
		$this->_wryFile = $this->_dirName . '/wry.dat';
		$this->_from = file_exists($this->_wryFile) ? 'wry' : 'index';
	}

	/**
	 * 根据域名地址获取IP所属地
	 * 
	 * @param string $domain
	 * @return string
	 */
	public function getIpFromByDomain($domain) {
		if (!$domain) return "Unknown";
		$ip = gethostbyname($domain);
		return $this->getIpFrom($ip);
	}
	
	/**
	 * 根据IP地址获取IP所属地
	 * 
	 * @param string $ip
	 * @return string
	 */
	public function getIpFrom($ip) {
		if (!$ip || !$this->_isCorrectIpAddress($ip)) return "Unknown";
		$action = sprintf('_getIpFrom%s',ucfirst($this->_from));
		if (!method_exists($this, $action)) {
			return "Unknown";
		}
		return $this->$action($ip);
	}
	
	/**
	 * 判断是否是正确的IP地址
	 * 
	 * @param string $ip
	 * @return boolean
	 */
	private function _isCorrectIpAddress($ip) {
		return preg_match('/^((25[0-5])|(2[0-4]\d)|(1\d\d)|([1-9]\d)|(\d))(\.((25[0-5])|(2[0-4]\d)|(1\d\d)|([1-9]\d)|(\d))){3}$/', $ip);
	}

	/**
	 * 从ip索引文件获取
	 * 
	 * @param string $ip
	 * @return string
	 */
	private function _getIpFromIndex($ip) {
		$IpTable = new IpTableIndex();
		return $IpTable->getIpFromIndex($ip);
	}

	/**
	 * 从ip纯真库获取
	 * 
	 * @param string $ip
	 * @return string
	 */
	private function _getIpFromWry($ip) {
		$IpTable = new IpTableWry();
		return $IpTable->getIpFromWry($ip);
	}
}

/**
 * 根据二分法从ip纯真库查找
 * 
 * @param $fp
 * @param $firstip
 * @param $lastip
 * @return $totalip
 */
class IpTableWry {
	
    private $_fp;
    private $_firstip;
    private $_lastip;
    private $_totalip;
    
    public function __construct($fileName = null) {
        $this->_fp = 0;
        $fileName = $fileName ? $fileName : Wind::getRealDir('REP:ipdata') . '/wry.dat';
        if (($this->_fp = fopen($fileName, 'rb')) !== false) {
            $this->_firstip = $this->getlong();
            $this->_lastip = $this->getlong();
            $this->_totalip = ($this->_lastip - $this->_firstip) / 7;
        }
    }

    /**
     * 根据所给 IP 地址或域名返回所在地区信息
     *
     * @param string $ip
     * @return string
     */
    public function getIpFromWry($ip) {
    	$unknowIp = "Unknown";
        if (!$this->_fp) return $unknowIp;  
        $ip = $this->packip($ip);
        // 二分法搜索索引区间
        $l = 0; 
        $u = $this->_totalip;
        $findip = $this->_lastip; 
        while ($l <= $u) { 
            $i = floor(($l + $u) / 2);
            fseek($this->_fp, $this->_firstip + $i * 7);
            $beginip = strrev(fread($this->_fp, 4));
            if ($ip < $beginip) { 
                $u = $i - 1;
            }
            else {
                fseek($this->_fp, $this->getlong3());
                $endip = strrev(fread($this->_fp, 4));
                if ($ip > $endip) { 
                    $l = $i + 1;
                }
                else { 
                    $findip = $this->_firstip + $i * 7;
                    break;
                }
            }
        }

        //获取查找到的IP地址
        fseek($this->_fp, $findip);
        $location['beginip'] = long2ip($this->getlong()); 
        $offset = $this->getlong3();
        fseek($this->_fp, $offset);
        $location['endip'] = long2ip($this->getlong());
        $byte = fread($this->_fp, 1);
        switch (ord($byte)) {
            case 1:                  
                $countryOffset = $this->getlong3();  
                fseek($this->_fp, $countryOffset);
                $byte = fread($this->_fp, 1); 
                switch (ord($byte)) {
                    case 2:   
                        fseek($this->_fp, $this->getlong3());
                        $country = $this->getstring();
                        fseek($this->_fp, $countryOffset + 4);
                        $area = $this->getarea();
                        break;
                    default:   
                        $country = $this->getstring($byte);
                        $area = $this->getarea();
                        break;
                }
                break;
            case 2:      
                fseek($this->_fp, $this->getlong3());
                $country = $this->getstring();
                fseek($this->_fp, $offset + 8);
                $area = $this->getarea();
                break;
            default:     
                $country = $this->getstring($byte);
                $area = $this->getarea();
                break;
        }
        if ($country == " CZ88.NET") { 
            $country = $unknowIp;
        }
        if ($area == " CZ88.NET") {
            $area = "";
        }
        return Pw::convert($country.$area, 'GBK');
    }
    
    /**
     * 返回读取的长整型数
     *
     * @return int
     */
    private function getlong() {
        $result = unpack('Vlong', fread($this->_fp, 4));
        return $result['long'];
    }

    /**
     * 返回读取的3个字节的长整型数
     *
     * @return int
     */
    private function getlong3() {
        $result = unpack('Vlong', fread($this->_fp, 3).chr(0));
        return $result['long'];
    }

    /**
     * 返回压缩后可进行比较的IP地址
     *
     * @param string $ip
     * @return string
     */
    private function packip($ip) {
        return pack('N', intval(ip2long($ip)));
    }

    /**
     * 返回读取的字符串
     *
     * @param string $data
     * @return string
     */
    private function getstring($data = '') {
        $char = fread($this->_fp, 1);
        while (ord($char) > 0) { 
            $data .= $char; 
            $char = fread($this->_fp, 1);
        }
        return $data;
    }

    /**
     * 返回地区信息
     *
     * @return string
     */
    private function getarea() {
        $byte = fread($this->_fp, 1); 
        switch (ord($byte)) {
            case 0: 
                $area = '';
                break;
            case 1:
                fseek($this->_fp, $this->getlong3());
                $area = $this->getstring();
                break;
            case 2:
                fseek($this->_fp, $this->getlong3());
                $area = $this->getstring();
                break;
            default: 
                $area = $this->getstring($byte);
                break;
        }
        return $area;
    }
}

/**
 * 根据索引文件从文件里查找
 * 
 * @param $_bsize
 * @param $_dirName
 * @param $_indexFile
 * @return string
 */
class IpTableIndex {
	private $_bsize;
	private $_dirName;
	private $_indexFile;

	/**
	 * @param $dirName
	 * @return unknown_type
	 */
	public function __construct($fileName = null) {
		$this->_bsize = 1024;
		$this->_dirName = $fileName ? dirname($fileName) : Wind::getRealDir('REP:ipdata');
		$this->_indexFile = $this->_dirName . '/ipindex.dat';
	}
	
	/**
	 * 通过ipindex从文本获取IP信息
	 * 
	 * @param $ip
	 * @return string
	 */
	public function getIpFromIndex($ip) {
		$unknowIp = "Unknown";
		$d_ip = explode('.', $ip);
		$txt = $this->_dirName . '/' . $d_ip[0] . '.txt';
		$tag_1 = $d_ip[0];
		$tag_2 = $d_ip[1];
		if (!file_exists($txt)) {
			$tag_1 = 0;
			$tag_2 = $d_ip[0];
			$txt = $this->_dirName . '/' . '0.txt';
		} else {
			$d_ip[0] = $d_ip[1];
			$d_ip[1] = $d_ip[2];
			$d_ip[2] = $d_ip[3];
			$d_ip[3] = '';
		}
		$ipIndex = $this->_getIPIndex($tag_1, $tag_2);
		if (empty($ipIndex)) {
			return $unknowIp;
		} elseif ($ipIndex[0] == -1) {
			$offset = 0;
			$offsize = filesize($txt);
		} else {
			$offset = $ipIndex[0];
			$offsize = $ipIndex[1] - $ipIndex[0];
		}
		if ($offsize < 1) return $unknowIp;
		if ($handle = @fopen($txt,'rb')) {
			flock($handle, LOCK_SH);
			fseek($handle, $offset, SEEK_SET);
			$d = "\n" . fread($handle, $offsize);
			$d .= fgets($handle, 100);
			$wholeIP = $d_ip[0] . '.' . $d_ip[1] . '.' . $d_ip[2];
			$d_ip[3] && $wholeIP .= '.' . $d_ip[3];
			$wholeIP = str_replace('255', '*', $wholeIP);
			$f = $l_d = 0;
			if (($s = strpos($d, "\n$wholeIP\t")) !== false) {
				$s = $s + $offset;
				fseek($handle, $s, SEEK_SET);
				$l_d = substr(fgets($handle, 100), 0, -1);
				$ip_a = explode("\t", $l_d);
				$ip_a[3] && $ip_a[2] .= ' ' . $ip_a[3];
				fclose($handle);
				return $ip_a[2];
			}
			$ip = $this->_d_ip($d_ip);
			while (!$f && !$l_d && ($wholeIP >= 0)) {
				if (($s = strpos($d, "\n" . $wholeIP . '.')) !== false) {
					$s = $s + $offset;
					list($l_d, $f) = $this->_s_ip($handle, $s, $ip);
					if ($f) return $f;
					while ($l_d && preg_match("/^\n$wholeIP/i", "\n" . $l_d) !== false) {
						list($l_d, $f) = $this->_s_ip($handle, $s, $ip, $l_d);
						if ($f) return $f;
					}
				}
				if (strpos($wholeIP, '.') !== false) {
					$wholeIP = substr($wholeIP, 0, strrpos(substr($wholeIP, 0, -1), '.'));
				} else {
					if ($txt == '0.txt') return $unknowIp;
					$wholeIP--;
				}
			}
		}
		return $unknowIp;
	}

	/**
	 * 从index.dat查找索引IP
	 * 
	 * @param $ip_1
	 * @param $ip_2
	 * @return array
	 */
	private function _getIPIndex($ip_1, $ip_2) {
		$index = array();
		if ($handle = @fopen($this->_indexFile, 'rb')) {
			$offset = ($ip_1 * $this->_bsize) + ($ip_2 * 4);
			fseek($handle, $offset, SEEK_SET);
			if (!feof($handle)) {
				$c1 = unpack('Nkey', fread($handle, 4));
				$c1 = $c1['key'];
				$c2 = 0;
				while (!feof($handle) && $c2 == 0) {
					$c2 = unpack('Nkey', fread($handle, 4));
					$c2 = $c2['key'];
				}
				if ($c1 != 0 && $c2 != 0) {
					$index = array($c1, $c2);
				}
			}
		} else {
			$index = array(-1, -1);
		}
		return $index;
	}

	/**
	 * 从index.dat查找索引IP
	 * 
	 * @param $db
	 * @param $s
	 * @param $ip
	 * @param $l_d
	 * @return unknown_type
	 */
	private function _s_ip($db, $s, $ip, $l_d = null) {
		if (empty($l_d)) {
			fseek($db, $s, SEEK_SET);
			$l_d = fgets($db, 100);
		}
		$ip_a = explode("\t", $l_d);
		$ip_a[0] = $this->_d_ip(explode('.', $ip_a[0]));
		$ip_a[1] = $this->_d_ip(explode('.', $ip_a[1]));
		if ($ip < $ip_a[0]) {
			$f = $l_d = '';
		} elseif ($ip >= $ip_a[0] && $ip <= $ip_a[1]) {
			fclose($db);
			$ip_a[3] && $ip_a[2] .= ' ' . $ip_a[3];
			$f = $ip_a[2];
			$l_d = '';
		} else {
			$f = '';
			$l_d = fgets($db, 100);
		}
		return array($l_d, $f);
	}

	/**
	 * @param $d_ip
	 * @return string
	 */
	private function _d_ip($d_ip) {
		$d_ips = '';
		foreach ($d_ip as $value) {
			$d_ips .= '.' . sprintf("%03d", str_replace('*', '255', $value));
		}
		return substr($d_ips, 1);
	}
}
?>