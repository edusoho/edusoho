<?php
namespace Topxia\WebBundle\Form\Common;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Topxia\WebBundle\DataDict\GenderDict;

class GenderType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $dataDict = new GenderDict();

        $resolver->setDefaults(array(
            'choices' => $dataDict->getGroupedDict(),
        ));
    }

    public function getParent()
    {
        return 'choice';
    }

    public function getName()
    {
        return 'gender';
    }
}