<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Finder\Finder;

class ServiceMethodCommand extends BaseCommand
{
    private $results = array();
    private $notices = array('error' => array(), 'info' => array());

    protected function configure()
    {
        $this
            ->setName('util:undeclared-service-method')
            ->setDescription('遍历使用了未申明的service方法')
            ->addArgument('folder', InputArgument::REQUIRED, 'folder');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $folder = $input->getArgument('folder');

        $this->loadAllService();
        $this->scandir($folder, $output);
    }

    protected function scandir($folder, $output)
    {
        $fileNames = scandir($folder);
        foreach ($fileNames as $fileName) {
            $file = $folder.DIRECTORY_SEPARATOR.$fileName;

            if (is_file($file) && substr($file, -strlen('.php')) !== '.php') {
                continue;
            }

            if (is_dir($file) && in_array($fileName, array('.', '..'))) {
                continue;
            }

            if (is_dir($file)) {
                $this->scandir($file, $output);
            } else {
                $this->printNoDeclaredServiceMethod($file, $output);
            }
        }

        // foreach ($this->notices['error'] as $key => $str) {
        // 	$output->writeln($str);
        // }

        // foreach ($this->notices['info'] as $key => $str) {
        // 	$output->writeln($str);
        // }
    }

    protected function loadAllService()
    {
        $finder = new Finder();
        $directory = realpath(dirname(dirname(__DIR__)).'/../Biz');
        $finder->directories()->in($directory)->depth('== 0');
        foreach ($finder as $file) {
            $folder = $file->getRealPath().DIRECTORY_SEPARATOR.'Service';
            if (!is_dir($folder)) {
                continue;
            }

            $services = $this->getServices($folder, $file->getRelativePathname());

            foreach ($services as $key => $service) {
                if (empty($this->results[$service])) {
                    $this->results[$service] = array();
                }

                $this->results[$service][] = $file->getRelativePathname().':'.$service;
            }
        }
    }

    protected function getServices($folder, $serviceNamespace)
    {
        $fileNames = scandir($folder);
        $service = array();
        foreach ($fileNames as $fileName) {
            $file = $folder.DIRECTORY_SEPARATOR.$fileName;

            if (is_file($file) && substr($file, -strlen('.php')) !== '.php') {
                continue;
            }

            if (is_dir($file) && in_array($fileName, array('.', '..'))) {
                continue;
            }

            if (!is_dir($file)) {
                $service[] = str_replace('.php', '', $fileName);
            }
        }

        return $service;
    }

    protected function printNoDeclaredServiceMethod($fileName, $output)
    {
        $file = file($fileName);
        foreach ($file as &$line) {
            // $partten = "/^http://([\w-]+\.)+[\w-]+(/[\w-./?%&=]*)?$/";
            $partten = '/get([a-zA-Z]*Service)\(\)->([a-zA-Z]\w*)\(/';
            preg_match_all($partten, $line, $matchs, PREG_SET_ORDER);
            if (!empty($matchs) && $fileName == '/Users/fengni/edusoho/www/edusoho/src/AppBundle/Controller/Admin/AnalysisController.php') {
                foreach ($matchs as $key => $value) {
                    $service = $value[1];

                    $serviceMap = array(
                        'CourseMemberService' => 'MemberService',
                        'CourseTaskService' => 'TaskService',
                        'CourseTaskResultService' => 'TaskResultService',
                        'CourseMaterialService' => 'MaterialService',
                        'NotifiactionService' => 'NotificationService',
                        'JobService' => 'CrontabService',
                        'NoteService' => 'CourseNoteService',
                    );

                    if (!empty($serviceMap[$service])) {
                        $service = $serviceMap[$service];
                    }

                    if (in_array($service, array('LevelService', 'VipLevelService', 'VipService'))) {
                        continue;
                    }

                    $method = $value[2];
                    if (empty($this->results[$service])) {
                        $this->notices['info'][] = sprintf('<info>file: %s, service: %s, method: %s</info>', $fileName, $service, $method);
                        // $output->writeln(sprintf('<error>file: %s, service: %s, method: %s</error>', $fileName, $service, $method));
                        continue;
                    }

                    $services = $this->results[$service];
                    $hasMethod = false;
                    foreach ($services as $key => $s) {
                        $rc = new \ReflectionClass($this->getServiceKernel()->createService($s));
                        if ($rc->hasMethod($method)) {
                            $hasMethod = true;
                            break;
                        }
                    }

                    if (!$hasMethod) {
                        $this->notices['error'][] = sprintf('<error>file: %s, service: %s, method: %s</error>', $fileName, $service, $method);
                        // $output->writeln(sprintf('<error>file: %s, service: %s, method: %s</error>', $fileName, $service, $method));
                    }
                }
            }
        }
    }
}
