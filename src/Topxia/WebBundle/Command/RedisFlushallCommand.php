<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Redis;


class RedisFlushallCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('Redis:flushall');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();
        $output->writeln('<info>开始清空Redis的所有key</info>');

        $this->flushallRedis($output);

        $output->writeln('<info>Redis清空完毕</info>');
    }

    protected function flushallRedis($output)
    {
        $redisConfigFile = $this->getContainer()->getParameter('kernel.root_dir').'/data/redis.php';

        if (!$redisConfigFile) {
            $output->writeln('<info>Redis未开启</info>');
        } else {
            $redisConfig  = include $redisConfigFile;
            $cnf = $redisConfig['default'];
            if (empty($cnf['servers'])) {
                $this->flushall($cnf);
            } else {
                foreach ($cnf['servers'] as $server) {
                    $this->flushall($server);
                }                
            }
        }
    }

    protected function flushall($cnf)
    {
        $redis = new Redis();
        $redis->pconnect($cnf['host'], $cnf['port'], $cnf['timeout'], $cnf['reserved'], $cnf['retry_interval']);
        $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
        $redis->flushall();
    }
}