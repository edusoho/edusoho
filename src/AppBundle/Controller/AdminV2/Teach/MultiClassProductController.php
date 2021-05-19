<?php

namespace AppBundle\Controller\AdminV2\Teach;

use AppBundle\Controller\AdminV2\BaseController;
use Symfony\Component\HttpFoundation\Request;

class MultiClassProductController extends BaseController
{
    public function indexAction(Request $request)
    {
        return $this->render('admin-v2/teach/multi_class_product/index.html.twig', [
        ]);
    }
}
