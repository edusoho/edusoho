<?php
namespace Fomalhaut\AcctSetupBundle\Process\Step;

use Sylius\Bundle\FlowBundle\Process\Context\ProcessContextInterface;
use Sylius\Bundle\FlowBundle\Process\Step\ControllerStep;

class BaseinfoStep extends ControllerStep
{
    public function displayAction(ProcessContextInterface $context)
    {
        $form = $this->createForm('wechat_acctsetup_baseinfo');
        $form->get('acctname')->setData('您的微信账号');
        $form->get('describe')->setData('对您微信的描述');;
        /*$form = $this->createFormBuilder()
            ->add('name','text')
            ->add('describe','text')
            ->add('nextStep', 'submit')
            ->getForm();

        $form->handleRequest($this->getRequest());

        if ($form->isValid()) {
            $data = $form->getData();
        }
        return $this->render('AcctSetupBundle:Prosess/Step:BaseinfoStep.html.twig', array(
            'form' => $form->createView(),
        ));*/
        return $this->render('AcctSetupBundle:Prosess/Step:BaseinfoStep.html.twig',
            array(
                'form' => $form->createView()
            )
        );
    }

    public function forwardAction(ProcessContextInterface $context)
    {
        $request = $this->getRequest();
        $form = $this->createForm('my_form');

        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
            $context->getStorage()->set('my_data', $form->getData());

            return $this->complete();
        }
        return $this->render('AcctSetupBundle:Prosess/Step:BaseinfoStep.html.twig', array(
            'form' => $form->createView(),
        ));
    }
} 