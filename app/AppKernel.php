<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Topxia\Service\Common\ServiceKernel;

class AppKernel extends Kernel {

    public function registerBundles () {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            // new JMS\SecurityExtraBundle\JMSSecurityExtraBundle() ,
            new Topxia\WebBundle\TopxiaWebBundle(),
            // new Leezy\PheanstalkBundle\LeezyPheanstalkBundle(),
            // new Avalanche\Bundle\ImagineBundle\AvalancheImagineBundle(),
            new Topxia\AdminBundle\TopxiaAdminBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            );


        if (in_array($this->getEnvironment(), array('dev' , 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }


    public function init () {
        date_default_timezone_set('Asia/Shanghai');
        parent::init();
    }

    public function boot ()
    {
        if (true === $this->booted) {
            return;
        }
        parent::boot();
        ServiceKernel::create($this->getContainer(), $this->getEnvironment(), $this->isDebug())
            ->setRootPath(realpath($this->getRootDir() . '/../'));
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
