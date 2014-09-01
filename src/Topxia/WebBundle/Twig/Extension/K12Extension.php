<?php
namespace Topxia\WebBundle\Twig\Extension;

use Topxia\Service\Common\ServiceKernel;
use Topxia\WebBundle\Util\CategoryBuilder;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Common\ConvertIpToolkit;

class K12Extension extends \Twig_Extension
{
    public function getFilters ()
    {
        return array(
        );
    }

    public function getFunctions()
    {
        return array(
            'check_class_permission' => new \Twig_Function_Method($this, 'checkClassPermission') ,
        );
    }

    public function checkClassPermission($name, $classId)
    {
        $classId = (is_array($classId) && isset($classId['id'])) ? $classId['id'] : $classId;
        return $this->getClassesService()->checkPermission($name, $classId);
    }

    protected function getClassesService()
    {
        return ServiceKernel::instance()->createService('Classes.ClassesService');
    }

    public function getName ()
    {
        return 'topxia_k12_twig';
    }

}

