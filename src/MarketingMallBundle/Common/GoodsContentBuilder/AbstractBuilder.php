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

    public function __construct(Biz $biz = null)
    {
        $this->biz = $biz;
    }

    public function setBiz(Biz $biz)
    {
        $this->biz = $biz;
    }
    abstract public function build($id);

    abstract public function builds($ids);

    protected function createNewException($exception)
    {
        if ($exception instanceof AbstractException) {
            throw $exception;
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

    public function transformImages($summary)
    {
        preg_match_all('/<img.*?src=[\"|\']?(.*?)[\"|\']*?\/?\s*>/i', $summary, $matches);
        if (empty($matches)) {
            return $summary;
        }
        $imgList = [];
        foreach ($matches[1] as $key => $imgUrl) {
            $imgList[] = AssetHelper::uriForPath($imgUrl);
        }
        return str_replace($matches[1], $imgList, $summary);
    }
}
