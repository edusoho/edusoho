<?php

namespace MarketingMallBundle\Common\GoodsContentBuilder;

use ApiBundle\Api\Util\AssetHelper;
use AppBundle\Common\Exception\AbstractException;
use Codeages\Biz\Framework\Context\Biz;

abstract class AbstractBuilder
{
    /**
     * @var Biz
     */
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    abstract public function build($id);

    protected function createNewException($e)
    {
        if ($e instanceof AbstractException) {
            throw $e;
        }

        throw new \Exception();
    }

    public function transformCover($cover, $default = 'course.png')
    {
        $cover['small'] = AssetHelper::getFurl(empty($cover['small']) ? '' : $cover['small'], $default);
        $cover['middle'] = AssetHelper::getFurl(empty($cover['middle']) ? '' : $cover['middle'], $default);
        $cover['large'] = AssetHelper::getFurl(empty($cover['large']) ? '' : $cover['large'], $default);

        return $cover;
    }
}
