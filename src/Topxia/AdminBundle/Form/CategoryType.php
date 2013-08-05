<?php

namespace Topxia\AdminBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\AbstractType;

class CategoryType extends AbstractType {
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('name', 'text');
		$builder->add('code', 'text');
		$builder->add('weight', 'text');
	}
	
	public function getName () {
        return 'category';
    }
}