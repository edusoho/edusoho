<?php

namespace Coupon\CouponBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CouponBundle:Default:index.html.twig', array('name' => $name));
    }
}
