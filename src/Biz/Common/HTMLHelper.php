<?php

namespace Biz\Common;

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

        $security = $this->getSettingService()->get('security');

        if (!empty($security['safe_iframe_domains'])) {
            $safeDomains = $security['safe_iframe_domains'];
        } else {
            $safeDomains = array();
        }

        $config = array(
            'cacheDir' => $this->biz['cache_directory'].'/htmlpurifier',
            'safeIframeDomains' => $safeDomains,
        );

        $factory = new HTMLPurifierFactory($config);
        $purifier = $factory->create($trusted);

        $html = $purifier->purify($html);
        $html = str_replace('http-equiv', '', $html);
        $html = $this->handleOuterLink($html, $safeDomains);

        if (!$trusted) {
            return $html;
        }
        $styles = $purifier->context->get('StyleBlocks');
        if ($styles) {
            $html = implode("\n", array(
                '<style type="text/css">',
                implode("\n", $styles),
                '</style>',
                $html,
            ));
        }

        return $html;
    }

    public function htmlTagAutoComple($html)
    {
        if (empty($html)) {
            return '';
        }

        $config = array(
            'cacheDir' => $this->biz['cache_directory'].'/htmlpurifier',
        );

        $factory = new HTMLPurifierFactory($config);
        $purifier = $factory->createSimple();

        return $purifier->purify($html);
    }

    protected function handleOuterLink($html, $safeDomains)
    {
        preg_match_all('/\<img[^\>]*?src\s*=\s*[\'\"](?:http:\/\/|https:\/\/)(.*?)[\'\"].*?\>/i', $html, $matches);
        foreach ($matches[1] as $key => $matche) {
            $needReplaceFlag = true;
            foreach ($safeDomains as $safeDomain) {
                if (false !== strpos($matche, $safeDomain)) {
                    $needReplaceFlag = false;
                }
            }
            //存在于白名单内就不进行替换移除
            if ($needReplaceFlag) {
                $html = str_replace($matches[0][$key], '', $html);
            }
        }

        return $html;
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
