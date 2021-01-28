<?php

namespace AppBundle\Component\QrCode;

class QrCodeWhiteListFilter
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function isInWhiteList($url)
    {
        $inWhitelist = false;
        $whiteProtocolList = $this->getQrCodeProtocolWhitelist();
        foreach ($whiteProtocolList as $protocol) {
            if (0 === strpos($url, $protocol.'://')) {
                $inWhitelist = true;
            }
        }

        $whiteSiteList = $this->getQrCodeSiteWhitelist();
        preg_match('/^(http:\/\/|https:\/\/)?([^\/|?]+)/i', $url, $match);
        foreach ($whiteSiteList as $site) {
            if ($site == $match[2]) {
                $inWhitelist = true;
            }
        }

        return $inWhitelist;
    }

    private function getQrCodeProtocolWhitelist()
    {
        $list = $this->container->getParameter('qrcode_protocol_whitelist_default');
        if ($this->container->hasParameter('qrcode_protocol_whitelist')) {
            $list = array_merge($list, $this->container->getParameter('qrcode_protocol_whitelist'));
        }

        return $list;
    }

    private function getQrCodeSiteWhitelist()
    {
        if ($this->container->hasParameter('qrcode_site_whitelist')) {
            return $this->container->getParameter('qrcode_site_whitelist');
        }

        return array();
    }
}
