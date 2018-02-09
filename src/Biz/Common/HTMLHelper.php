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
        $result = preg_match('/\<img.*?src\s*=\s*[\'\"](http:\/\/|https:\/\/)(.*?)[\'\"].*?\>/i', $html, $matches);
        if ($result && !in_array($matches[2], $safeDomains)) {
            $html = preg_replace('/\<img.*?src\s*=\s*[\'\"](http:\/\/|https:\/\/)(.*?)[\'\"].*?\>/i', '', $html);
        }
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

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
