<?php
namespace Topxia\WebBundle\Form;

use Topxia\WebBundle\Form\Util\TaxonomyTermChoices;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

class ActivityMemberType extends AbstractType
{

    public function buildForm (FormBuilderInterface $builder, array $options)
    {
        $builder->add("email","text");
        $builder->add("nickname","text");
        $builder->add("truename","text");
        $builder->add("mobile","text");
        $builder->add("company","text");
        $builder->add("job","text");
        $builder->add("aboutInfo","text");
        $builder->add("question","text");
        $builder->add("joinMode","text");
        $builder->add("approvalStatus","text");
    }

    public function getName ()
    {
        return 'activitymember';
    }

}