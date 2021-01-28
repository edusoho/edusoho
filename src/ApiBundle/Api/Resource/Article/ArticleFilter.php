<?php

namespace ApiBundle\Api\Resource\Article;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\AssetHelper;
use ApiBundle\Api\Util\TagUtil;

class ArticleFilter extends Filter
{
    protected $publicFields = array(
        'id',
        'title',
        'categoryId',
        'tagIds',
        'sourceUrl',
        'publishedTime',
        'body',
        'thumb',
        'originalThumb',
        'picture',
        'hits',
        'status',
        'featured',
        'promoted',
        'sticky',
        'postNum',
        'upsNum',
        'userId',
        'orgId',
        'orgCode',
        'createdTime',
        'updatedTime',
    );

    protected function publicFields(&$data)
    {
        $data['publishedTime'] = date('c', $data['publishedTime']);
        $data['thumb'] = AssetHelper::getFurl($data['thumb']);
        $data['originalThumb'] = AssetHelper::getFurl($data['originalThumb']);
        $data['picture'] = AssetHelper::getFurl($data['picture']);
        $data['body'] = $this->convertAbsoluteUrl($data['body']);
        $data['tags'] = TagUtil::buildTags('article', $data['id']);
    }
}
