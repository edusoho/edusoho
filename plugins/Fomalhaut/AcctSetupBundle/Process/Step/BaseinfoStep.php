<?php
namespace Fomalhaut\AcctSetupBundle\Process\Step;

use Sylius\Bundle\FlowBundle\Process\Context\ProcessContextInterface;
use Sylius\Bundle\FlowBundle\Process\Step\ControllerStep;

class BaseinfoStep extends AbstractStep
{
    public function displayAction(ProcessContextInterface $context)
    {
        return $this->render(
            'AcctSetupBundle:Prosess/Step:BaseinfoStep.html.twig',
            array('form' => $this->createForm('wechat_acctsetup_baseinfo')->createView())
        );
    }

    public function forwardAction(ProcessContextInterface $context)
    {
        $request = $this->getRequest();
        $form = $this->createForm('wechat_acctsetup_baseinfo');

        if ($form->handleRequest($request)->isValid()) {
            $data = $form->getData();
            //$context->getStorage()->set('my_data', $form->getData());
            $wecaht = $this->getWechatService();
            return $this->complete();
        }
        return $this->render(
            'AcctSetupBundle:Prosess/Step:BaseinfoStep.html.twig',
            array('form' => $form->createView())
        );
    }

    private function getWechatService()
    {
        return $this->getServiceKernel()->createService('Fomalhaut:Wechat.AcctountService');
    }

} 