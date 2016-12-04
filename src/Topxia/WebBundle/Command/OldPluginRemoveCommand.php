<?php
namespace Topxia\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\User\CurrentUser;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\PluginUtil;

class OldPluginRemoveCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->addArgument(
                'bundlename',
                InputArgument::OPTIONAL,
                '插件名称?'
            )
            ->setName('old-plugin:remove')
            ->setDescription('移除插件模板')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {   
        $this->initServiceKernel();

        $name = $input->getArgument('bundlename');

        if (!$name) {
            throw new \RuntimeException("插件名称不能为空！");
        }

        if (!preg_match('/^[a-zA-Z\s]+$/', $name)) {
            throw new \RuntimeException("插件名称只能为英文！");
        }

        $output->writeln("<comment>正在移除插件`{$name}`：</comment>");

        $pluginDir = dirname($this->getContainer()->getParameter('kernel.root_dir')) . '/plugins/' . $name;
        if (!is_dir($pluginDir)) {
            throw new \RuntimeException("插件目录{$pluginDir}不存在！");
        }

        $output->writeln("<comment>  - 移除插件目录...</comment><info>OK</info>");

        $this->deleteDir($pluginDir);

        $app = $this->getAppService()->uninstallApp($name);
        $output->writeln("<comment>  - 移除应用记录...</comment><info>OK</info>");

        PluginUtil::refresh();
        $output->writeln("<comment>  - 刷新插件缓存...</comment><info>OK</info>");

        
    }

    private function deleteDir($dir) {
        //先删除目录下的文件：
        $dh = opendir($dir);

        while ($file = readdir($dh)) {

            if($file != "." && $file != "..") {

              $fullpath = $dir."/".$file;

              if(!is_dir($fullpath)) {

                  unlink($fullpath);

              } else {

                  $this->deleteDir($fullpath);
              }
            }
        }
         
        closedir($dh);
        //删除当前文件夹：
        if(rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

}