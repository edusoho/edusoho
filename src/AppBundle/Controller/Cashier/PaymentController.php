<?php

namespace AppBundle\Controller\Cashier;

use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

abstract class PaymentController extends BaseController
{
    abstract public function payAction($trade);

    abstract public function notifyAction(Request $request, $payment);
}
