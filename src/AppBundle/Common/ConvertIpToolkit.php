<?php

namespace AppBundle\Common;

class ConvertIpToolkit
{
    //https://www.ipip.net/product/ip.html
    private static $ip = null;

    private static $fp = null;
    private static $offset = null;
    private static $index = null;

    public static function convertIps(array $array)
    {
        $result = array();
        foreach ($array as $key => $value) {
            $location = static::convertIp($value['ip']);
            if ('N/A' == $location) {
                $location = '未知区域';
            }

            $value['location'] = $location;
            $result[$key] = $value;
        }

        return $result;
    }

    public static function convertIp($ip)
    {
        if (true === empty($ip)) {
            return 'N/A';
        }

        $nip = gethostbyname($ip);
        $ipdot = explode('.', $nip);

        if ($ipdot[0] < 0 || $ipdot[0] > 255 || 4 !== count($ipdot)) {
            return 'N/A';
        }

        if (null === self::$fp) {
            self::init();
        }

        $nip2 = pack('N', ip2long($nip));

        $tmp_offset = ((int) $ipdot[0] * 256 + (int) $ipdot[1]) * 4;
        $start = unpack('Vlen', self::$index[$tmp_offset].self::$index[$tmp_offset + 1].self::$index[$tmp_offset + 2].self::$index[$tmp_offset + 3]);

        $index_offset = $index_length = null;
        $max_comp_len = self::$offset['len'] - 262144 - 4;
        for ($start = $start['len'] * 9 + 262144; $start < $max_comp_len; $start += 9) {
            if (self::$index[$start].self::$index[$start + 1].self::$index[$start + 2].self::$index[$start + 3] >= $nip2) {
                $index_offset = unpack('Vlen', self::$index[$start + 4].self::$index[$start + 5].self::$index[$start + 6]."\x0");
                $index_length = unpack('nlen', self::$index[$start + 7].self::$index[$start + 8]);

                break;
            }
        }

        if (null === $index_offset) {
            return 'N/A';
        }

        fseek(self::$fp, self::$offset['len'] + $index_offset['len'] - 262144);
        $locations = explode("\t", fread(self::$fp, $index_length['len']));
        $location = '';
        foreach ($locations as $key => $value) {
            if (0 == $key) {
                $location = $value;
            } else {
                $location .= $locations[$key - 1] == $value ? '' : $value;
            }
        }

        return $location;
    }

    private static function init()
    {
        if (null === self::$fp) {
            self::$ip = new self();

            self::$fp = fopen(__DIR__.'/tinyipdata.datx', 'rb');
            if (false === self::$fp) {
                throw new Exception('Invalid tinyipdata.datx file!');
            }

            self::$offset = unpack('Nlen', fread(self::$fp, 4));
            if (self::$offset['len'] < 4) {
                throw new Exception('Invalid tinyipdata.datx file!');
            }

            self::$index = fread(self::$fp, self::$offset['len'] - 4);
        }
    }

    public function __destruct()
    {
        if (null !== self::$fp) {
            fclose(self::$fp);

            self::$fp = null;
        }
    }
}
