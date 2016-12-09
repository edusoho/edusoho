<?php


namespace Biz\Common;


use Codeages\Biz\Framework\Context\Biz;
use Topxia\Service\Util\HTMLPurifierFactory;

class HTMLHelpful
{
    /**
     * @var $biz Biz
     */
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function purify($html, $trusted=false)
    {
        if (empty($html)) {
            return '';
        }

        $config = array(
            'cacheDir' => $this->biz['kernel.cache_dir'] . '/htmlpurifier'
        );

        $factory  = new HTMLPurifierFactory($config);
        $purifier = $factory->create($trusted);

        return $purifier->purify($html);
    }
}