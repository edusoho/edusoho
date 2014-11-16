<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2014/11/15
 * Time: 13:04
 */

namespace Fomalhaut\AcctSetupBundle\Process\Step;

use Sylius\Bundle\FlowBundle\Process\Context\ProcessContextInterface;
use Sylius\Bundle\FlowBundle\Process\Step\ControllerStep;

class OneclickStep extends ControllerStep
{
    public function displayAction(ProcessContextInterface $context)
    {
        return $this->render(
            'AcctSetupBundle:Prosess/Step:OneclickStep.html.twig',
            array('form' => $this->createForm('wechat_acctsetup_oneclick')->createView())
        );
    }

    public function forwardAction(ProcessContextInterface $context)
    {}
} 