<?php

namespace AppBundle\Command;

use Biz\Role\Util\PermissionBuilder;
use Biz\User\CurrentUser;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Queue\Worker;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class WorkerCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('queue:work')
            ->setDescription('Start processing jobs on the queue')
            ->addArgument('name', InputArgument::REQUIRED, 'Queue name')
            ->addArgument('process-no', InputArgument::REQUIRED, 'Process No.')
            ->addOption('once', null, InputOption::VALUE_NONE, 'Only process the next job on the queue')
            ->addOption('tries', null, InputOption::VALUE_OPTIONAL, 'The number of seconds a child process can run', 0)
            ->addOption('stop-when-idle', null, InputOption::VALUE_NONE, 'Worker stop when no jobs');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queueName = $input->getArgument('name') ?: 'default';
        $biz = $this->getBiz();
        $queue = $biz['queue.connection.'.$queueName];

        $options = array(
            'once' => $input->getOption('once'),
            'stop_when_idle' => $input->getOption('stop-when-idle'),
            'tries' => (int) $input->getOption('tries'),
            'lock_file' => sprintf('%s/queue-worker-%s-%s.lock', $biz['run_dir'], $queueName, $input->getArgument('process-no')),
        );

        $this->initServiceKernel();
        $lock = $biz['lock.factory']->createLock($options['lock_file']);

        $worker = new Worker($queue, $biz['queue.failer'], $lock, $biz['logger'], $options);
        $worker->run();
    }

    protected function initServiceKernel()
    {
        $_SERVER['HTTP_HOST'] = '127.0.0.1';
        $serviceKernel = $this->getServiceKernel();

        $currentUser = new CurrentUser();
        $systemUser = $this->getUserService()->getUserByType('system');
        $systemUser['currentIp'] = '127.0.0.1';

        $currentUser->fromArray($systemUser);
        $currentUser->setPermissions(PermissionBuilder::instance()->getPermissionsByRoles($currentUser->getRoles()));
        $serviceKernel->setCurrentUser($currentUser);
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User:UserService');
    }
}
