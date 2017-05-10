<?php

namespace CustomBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;

/**
 * Created by PhpStorm.
 * User: Allen
 * Date: 09/05/2017
 * Time: 20:51
 */
class DefaultController extends \CustomBundle\Controller\DefaultController
{
    public function IndexAction(Request $request)
    {
        return $this->render('admin/default/index.html.twig');
    }
}