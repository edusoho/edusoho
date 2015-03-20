<?php
namespace Topxia\WebBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;

class TagsToIdsTransformer implements DataTransformerInterface
{
    public function transform($tagIds)
    {
        if (empty($tagIds) or !is_array($tagIds)) {
            return '';
        }

        $tags = $this->getTagService()->findTagsByIds($tagIds);
        return implode(',', ArrayToolkit::column($tags, 'name'));
    }

    public function reverseTransform($tags)
    {
        if (empty($tags)) {
            return array();
        }

        $tags = explode(',', $tags);

        $tags = $this->getTagService()->findTagsByNames($tags);
        return ArrayToolkit::column($tags, 'id');
    }

    protected function getTagService()
    {
        return ServiceKernel::instance()->createService('Taxonomy.TagService');
    }
}