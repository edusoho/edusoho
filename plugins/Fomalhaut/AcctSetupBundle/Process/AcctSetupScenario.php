<?php
namespace Fomalhaut\AcctSetupBundle\Process;

use Sylius\Bundle\FlowBundle\Process\Builder\ProcessBuilderInterface;
use Sylius\Bundle\FlowBundle\Process\Scenario\ProcessScenarioInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class AcctSetupScenario extends ContainerAware implements ProcessScenarioInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(ProcessBuilderInterface $builder)
    {
        $builder
            ->add('baseinfo', new Step\BaseinfoStep())
            ->add('oneclick', new Step\OneclickStep())
            ->add('setup', new Step\SetupStep())
            //->add('advanced', new Step\AdvancedStep())
            ->add('guide', new Step\GuideStep())
            //->setRedirect('homepage')
        ;
    }
} 