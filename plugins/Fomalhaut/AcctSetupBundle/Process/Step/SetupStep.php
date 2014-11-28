<?php
namespace Fomalhaut\AcctSetupBundle\Process\Step;

use Sylius\Bundle\FlowBundle\Process\Context\ProcessContextInterface;

class SetupStep extends AbstractStep
{
    public function displayAction(ProcessContextInterface $context)
    {
        return $this->render(
            'AcctSetupBundle:Prosess/Step:SetupStep.html.twig',
            array('form' => $this->createForm('wechat_acctsetup_setup')->createView())
        );
    }

    public function forwardAction(ProcessContextInterface $context)
    {
        $request = $this->getRequest();
        $form = $this->createForm('wechat_acctsetup_setup');

        if ($form->handleRequest($request)->isValid()) {

            return $this->complete();
        }

        return $this->render(
            'AcctSetupBundle:Prosess/Step:SetupStep.html.twig',
            array('form' => $this->createForm('wechat_acctsetup_setup')->createView())
        );
    }
}