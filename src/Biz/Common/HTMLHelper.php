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

        $security = $this->getSettingService()->get('safe_iframe_domains');

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

        return $purifier->purify($html);
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
