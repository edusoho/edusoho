<?php

namespace AppBundle\Common;

class DeviceToolkit
{
    /**
     * 是否移动端访问访问.
     *
     * @return bool
     */
    public static function isMobileClient()
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }

        //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset($_SERVER['HTTP_VIA'])) {
            //找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
        }

        //判断手机发送的客户端标志,兼容性有待提高
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array(
                'nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp',
                'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu',
                'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi',
                'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile',
            );

            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match('/('.implode('|', $clientkeywords).')/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }

        //协议法，因为有可能不准确，放到最后判断
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((false !== strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml')) && (false === strpos($_SERVER['HTTP_ACCEPT'], 'text/html') || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }

        return false;
    }

    public static function isIOSClient()
    {
        //判断手机发送的客户端标志,兼容性有待提高
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array(
                'iphone', 'ipod',
            );

            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match('/('.implode('|', $clientkeywords).')/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }

        return false;
    }

    public static function getMobileDeviceType($userAgent)
    {
        $userAgent = strtolower($userAgent);

        $ios = array('iphone', 'ipad', 'ipod');
        foreach ($ios as $keyword) {
            if (strpos($userAgent, $keyword) > -1) {
                return 'ios';
            }
        }

        if (strpos($userAgent, 'Android') > -1) {
            return 'android';
        }

        return 'unknown';
    }

    public static function getBrowse()
    {
        try {
            return self::matchBrowser();
        } catch (\Exception $e) {
            return '未知浏览器';
        }
    }

    private static function matchBrowser()
    {
        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            return '未知浏览器';
        }

        $agent = $_SERVER['HTTP_USER_AGENT'];  //获取用户代理字符串

        if (false !== stripos($agent, 'Firefox/')) {
            preg_match("/Firefox\/([^;)]+)+/i", $agent, $version);
            $exp[0] = 'Firefox';
            $exp[1] = $version[1];  //获取火狐浏览器的版本号
        } elseif (false !== stripos($agent, 'Maxthon')) {
            preg_match("/Maxthon\/([\d\.]+)/", $agent, $version);
            $exp[0] = '傲游';
            $exp[1] = $version[1];
        } elseif (false !== stripos($agent, 'MSIE')) {
            preg_match("/MSIE\s+([^;)]+)+/i", $agent, $version);
            $exp[0] = 'IE';
            $exp[1] = $version[1];  //获取IE的版本号
        } elseif (false !== stripos($agent, 'Opera')) {
            preg_match("/Opera\/([\d\.]+)/", $agent, $version);
            $exp[0] = 'Opera';
            $exp[1] = $version[1];
        } elseif (false !== stripos($agent, 'Edge')) {
            //win10 Edge浏览器 添加了chrome内核标记 在判断Chrome之前匹配
            preg_match("/Edge\/([\d\.]+)/", $agent, $version);
            $exp[0] = 'Edge';
            $exp[1] = $version[1];
        } elseif (false !== stripos($agent, 'QQBrowserLite')) {
            preg_match("/QQBrowserLite\/([\d\.]+)/", $agent, $version);
            $exp[0] = 'QQ Lite浏览器';
            $exp[1] = $version[1];
        } elseif (false !== stripos($agent, 'QQBrowser')) {
            preg_match("/QQBrowser\/([\d\.]+)/", $agent, $version);
            $exp[0] = 'QQ浏览器';
            $exp[1] = $version[1];
        } elseif (false !== stripos($agent, 'Chrome')) {
            preg_match("/Chrome\/([\d\.]+)/", $agent, $version);
            $exp[0] = 'Chrome';
            $exp[1] = $version[1];  //获取google chrome的版本号
        } elseif (false !== stripos($agent, 'rv:') && false !== stripos($agent, 'Gecko')) {
            preg_match("/rv:([\d\.]+)/", $agent, $version);
            $exp[0] = 'IE';
            $exp[1] = $version[1];
        } elseif (false !== stripos($agent, 'MetaSr')) {
            preg_match("/MetaSr([\d\.]+)/", $agent, $version);
            $exp[0] = '搜狗浏览器';
            $exp[1] = $version[1];
        } elseif (false !== stripos($agent, '360SE')) {
            preg_match("/360SE([\d\.]+)/", $agent, $version);
            $exp[0] = '360浏览器';
            $exp[1] = $version[1];
        } elseif (false !== stripos($agent, 'safari/')) {
            preg_match('/safari\/([^\s]+)/i', $agent, $version);
            $exp[0] = 'Safari';
            $exp[1] = $version[1];
        } elseif (false !== stripos($agent, 'OmniWeb/')) {
            preg_match('/OmniWeb\/(v*)([^\s|;]+)/i', $agent, $version);
            $exp[0] = 'OmniWeb';
            $exp[1] = $version[1];
        } else {
            $exp[0] = '未知浏览器';
            $exp[1] = '';
        }

        return $exp[0].'('.$exp[1].')';
    }

    public static function getOperatingSystem()
    {
        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            return '未知操作系统';
        }

        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (stripos($agent, 'win') && stripos($agent, '95')) {
            $os = 'Windows 95';
        } elseif (stripos($agent, 'win 9x') && stripos($agent, '4.90')) {
            $os = 'Windows ME';
        } elseif (stripos($agent, 'win') && stripos($agent, '98')) {
            $os = 'Windows 98';
        } elseif (stripos($agent, 'win') && stripos($agent, 'nt 5.1')) {
            $os = 'Windows XP';
        } elseif (stripos($agent, 'win') && stripos($agent, 'nt 5')) {
            $os = 'Windows 2000';
        } elseif (stripos($agent, 'win') && stripos($agent, 'nt')) {
            $os = 'Windows NT';
        } elseif (stripos($agent, 'win') && stripos($agent, '32')) {
            $os = 'Windows 32';
        } elseif (stripos($agent, 'linux')) {
            $os = 'Linux';
        } elseif (stripos($agent, 'unix')) {
            $os = 'Unix';
        } elseif (stripos($agent, 'sun') && stripos($agent, 'os')) {
            $os = 'SunOS';
        } elseif (stripos($agent, 'ibm') && stripos($agent, 'os')) {
            $os = 'IBM OS/2';
        } elseif (stripos($agent, 'Mac') && stripos($agent, 'PC')) {
            $os = 'Macintosh';
        } elseif (stripos($agent, 'PowerPC')) {
            $os = 'PowerPC';
        } elseif (stripos($agent, 'AIX')) {
            $os = 'AIX';
        } elseif (stripos($agent, 'HPUX')) {
            $os = 'HPUX';
        } elseif (stripos($agent, 'NetBSD')) {
            $os = 'NetBSD';
        } elseif (stripos($agent, 'BSD')) {
            $os = 'BSD';
        } elseif (stripos($agent, 'OSF1')) {
            $os = 'OSF1';
        } elseif (stripos($agent, 'IRIX')) {
            $os = 'IRIX';
        } elseif (stripos($agent, 'FreeBSD')) {
            $os = 'FreeBSD';
        } elseif (stripos($agent, 'teleport')) {
            $os = 'teleport';
        } elseif (stripos($agent, 'flashget')) {
            $os = 'flashget';
        } elseif (stripos($agent, 'webzip')) {
            $os = 'webzip';
        } elseif (stripos($agent, 'offline')) {
            $os = 'offline';
        } else {
            $os = '未知操作系统';
        }

        return $os;
    }
}
