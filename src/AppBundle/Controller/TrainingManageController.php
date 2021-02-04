<?php

namespace AppBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Biz\Common\CommonException;
use Biz\Question\Service\CategoryService;
use Symfony\Component\HttpFoundation\Request;

class TrainingManageController extends BaseController
{
    public function imagesPickerAction(Request $request, $id)
    {
        return $this->render('training/manage/images-modal.html.twig');
    }

    public function imagesPickerdAction(Request $request, $id){
        echo 18;die;
    }
}
