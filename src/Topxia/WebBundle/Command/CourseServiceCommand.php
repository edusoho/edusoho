<?php

namespace Topxia\WebBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Codeages\PluginBundle\System\PluginRegister;
use Topxia\Service\Util\PluginUtil;

class CourseServiceCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('util:course-service-compaire');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $biz = $this->getContainer()->get('biz');
        $courseService = $biz->service("Course:CourseService");
        $bizCourseService = new \ReflectionClass($courseService);
        $methods = $bizCourseService->getMethods();
        $topxiaMethods = array();
        foreach ($methods as $method) {
            $topxiaMethods[] = $method->getName();
        }

        

        $topxiaCourseService = new \ReflectionClass(new \Topxia\Service\Course\Impl\CourseServiceImpl());
        $methods = $topxiaCourseService->getMethods();

        $existMethods = array();
        $notExistMethods = array();
        foreach ($methods as $method) {
            if(in_array($method->getName(), $topxiaMethods)) {
                $existMethods[] = $method->getName();
            } else {
                $notExistMethods[] = $method->getName();
            }
        }

        foreach ($existMethods as $existMethod) {
            $output->writeln("<info>{$existMethod}</info>");
        }
        foreach ($notExistMethods as $existMethod) {
            $output->writeln("<error>{$existMethod}</error>");
        }

    }

}
