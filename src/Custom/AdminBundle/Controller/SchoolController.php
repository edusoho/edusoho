<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/17
 * Time: 10:27
 */

namespace Custom\AdminBundle\Controller;


class SchoolController extends BaseController
{
    public function indexAction()
    {
        return $this->render('TopxiaAdminBundle:School:index.html.twig');
    }
}