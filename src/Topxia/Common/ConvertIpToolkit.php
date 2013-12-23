<?php
namespace Topxia\Common;

class ConvertIpToolkit
{
	public static function convertIps (array $array)
	{
		$result = array();
		foreach ($array as $key => $value) {
			$value['location'] = self::convertIp($value['ip']);
			$result[$key] = $value;
		}
		return $result;
	}

	public static function convertIp ($ip)
	{
		static $fp = NULL, $offset = array(), $index = NULL;

		$ips = explode('.', $ip);
		$ip = pack('N', ip2long($ip));

		$ips[0] = (int)$ips[0];
		$ips[1] = (int)$ips[1];

		$tinyipdataPath = __DIR__.'/tinyipdata.dat';
		if($fp = @fopen($tinyipdataPath, 'rb')) {
			$offset = @unpack('Nlen', @fread($fp, 4));
			$index  = @fread($fp, $offset['len'] - 4);
		} else {
			throw new \Exception("无法打开tinyipdata文件", 1);
		}

		$length = $offset['len'] - 1028;
		$start  = @unpack('Vlen', $index[$ips[0] * 4] . $index[$ips[0] * 4 + 1] . $index[$ips[0] * 4 + 2] . $index[$ips[0] * 4 + 3]);

		for ($start = $start['len'] * 8 + 1024; $start < $length; $start += 8) {

			if ($index{$start} . $index{$start + 1} . $index{$start + 2} . $index{$start + 3} >= $ip) {
				$index_offset = @unpack('Vlen', $index{$start + 4} . $index{$start + 5} . $index{$start + 6} . "\x0");
				$index_length = @unpack('Clen', $index{$start + 7});
				break;
			}
		}

		@fseek($fp, $offset['len'] + $index_offset['len'] - 1024);

		if($index_length['len']) {
			$result = @fread($fp, $index_length['len']);
			return $result;
		} else {
			return '查询不到此IP';
		}
	}
}