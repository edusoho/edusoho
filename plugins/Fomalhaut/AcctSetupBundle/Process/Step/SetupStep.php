<?php
namespace Fomalhaut\AcctSetupBundle\Process\Step;

use Sylius\Bundle\FlowBundle\Process\Context\ProcessContextInterface;

class OneclickStep extends AbstractStep
{
    public function displayAction(ProcessContextInterface $context)
    {
        return $this->render(
            'AcctSetupBundle:Prosess/Step:SetupStep.html.twig',
            array('form' => $this->createForm('wechat_acctsetup_Setup')->createView())
        );
    }

    public function forwardAction(ProcessContextInterface $context)
    {}
}