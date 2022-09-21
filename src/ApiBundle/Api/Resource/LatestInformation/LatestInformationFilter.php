<?php

namespace ApiBundle\Api\Resource\LatestInformation;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\AssetHelper;

class LatestInformationFilter extends Filter
{
    protected $publicFields = array(
        "id",
        "title",
        "categoryId",
        "tagIds",
        "source",
        "sourceUrl",
        "publishedTime",
        "body",
        "thumb",
        "originalThumb",
        "picture",
        "status",
        "hits",
        "featured",
        "promoted",
        "sticky",
        "postNum",
        "upsNum",
        "userId",
        "orgId",
        "orgCode",
        "createdTime",
        "updatedTime",
    );

    protected function publicFields(&$info)
    {
        $info['body'] = AssetHelper::transformImages($info['body']);
        $info['thumb'] = AssetHelper::transformImagesAddUrl($info['thumb'], '');
        $info['originalThumb'] = AssetHelper::transformImagesAddUrl($info['originalThumb'], '');
        $info['picture'] = AssetHelper::transformImagesAddUrl($info['picture'], 'picture');
    }
}