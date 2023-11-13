<?php

namespace Biz\Common;

use AppBundle\Common\UrlToolkit;
use Biz\System\Service\CacheService;
use Biz\System\Service\SettingService;
use Biz\Util\HTMLPurifierFactory;
use Codeages\Biz\Framework\Context\Biz;

class HTMLHelper
{
    /**
     * @var Biz
     */
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function purify($html, $trusted = false)
    {
        if (!isset($html)) {
            return '';
        }

        $safeDomains = $this->getCacheService()->get('safe_iframe_domains') ?: [];

        $config = [
            'cacheDir' => $this->biz['cache_directory'].'/htmlpurifier',
            'safeIframeDomains' => $safeDomains,
        ];

        $factory = new HTMLPurifierFactory($config);
        $purifier = $factory->create($trusted);

        $html = $purifier->purify($html);
        $html = str_replace('http-equiv', '', $html);
        $html = $this->handleOuterLink($html, $safeDomains);
        $html = $this->filterInvalidImgSrc($html);

        if (!$trusted) {
            return $html;
        }
        $styles = $purifier->context->get('StyleBlocks');
        if ($styles) {
            $html = implode("\n", [
                '<style type="text/css">',
                implode("\n", $styles),
                '</style>',
                $html,
            ]);
        }

        return $html;
    }

    public function htmlTagAutoComple($html)
    {
        if (empty($html)) {
            return '';
        }

        $config = [
            'cacheDir' => $this->biz['cache_directory'].'/htmlpurifier',
        ];

        $factory = new HTMLPurifierFactory($config);
        $purifier = $factory->createSimple();

        return $purifier->purify($html);
    }

    public function closeTags($html)
    {
        preg_match_all('#<(?!meta|img|br|hr|input\b)\b([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
        $openedtags = $result[1];

        preg_match_all('#</([a-z]+)>#iU', $html, $result);
        $closedtags = $result[1];
        $len_opened = count($openedtags);
        if (count($closedtags) == $len_opened) {
            return $html;
        }

        $openedtags = array_reverse($openedtags);
        for ($i = 0; $i < $len_opened; ++$i) {
            if (!in_array($openedtags[$i], $closedtags)) {
                $html .= '</'.$openedtags[$i].'>';
            } else {
                unset($closedtags[array_search($openedtags[$i], $closedtags)]);
            }
        }

        return $html;
    }

    protected function handleOuterLink($html, $safeDomains)
    {
        $siteSettings = $this->getSettingService()->get('site', []);
        $url = isset($siteSettings['url']) ? $this->getTrimUrl($siteSettings['url']) : '';

        preg_match_all('/\<img[^\>]*?src\s*=\s*[\'\"](?:http:\/\/|https:\/\/)(.*?)[\'\"].*?\>/i', $html, $matches);
        foreach ($matches[1] as $key => $matche) {
            $needReplaceFlag = true;
            foreach ($safeDomains as $safeDomain) {
                if (false !== strpos($matche, $safeDomain)) {
                    $needReplaceFlag = false;
                }
            }

            if (empty($url) || false !== strpos($matche, $url)) {
                $needReplaceFlag = false;
            }

            //存在于白名单内就不进行替换移除
            if ($needReplaceFlag) {
                $html = str_replace($matches[0][$key], '', $html);
            }
        }

        return $html;
    }

    protected function filterInvalidImgSrc($html)
    {
        $siteSettings = $this->getSettingService()->get('site', []);
        $siteUrl = isset($siteSettings['url']) ? $this->getTrimUrl($siteSettings['url']) : '';
        preg_match_all('/\<img[^\>]*?src\s*=\s*[\'\"](?:http:\/\/|https:\/\/)?(.*?)[\'\"].*?\>/i', $html, $matches);
        $webDir = $this->biz['kernel.root_dir'].'/../web';
        foreach ($matches[1] as $key => $match) {
            if (0 === strpos($match, '/')) {
                $match = preg_replace('/\?version=[\d.]+/', '', $match);
                $imgPath = $webDir.$match;
            }
            if (!empty($siteUrl) && 0 === strpos($match, $siteUrl)) {
                $imgPath = $webDir.str_replace($siteUrl, '', $match);
            }
            if (!empty($imgPath) && !file_exists($imgPath)) {
                $html = str_replace($matches[0][$key], '', $html);
                $imgPath = '';
            }
        }

        return $html;
    }

    protected function getTrimUrl($url)
    {
        $url = !empty($url) ? $url : '';
        $url = rtrim($url, '/');

        return UrlToolkit::ltrimHttpProtocol($url);
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    /**
     * @return CacheService
     */
    private function getCacheService()
    {
        return $this->biz->service('System:CacheService');
    }
}
