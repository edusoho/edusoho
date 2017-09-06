<?php

namespace AppBundle\Command;

use AppBundle\Common\BlockToolkit;
use Biz\Util\PluginUtil;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ThemeRegisterCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('theme:register')
            ->addArgument('code', InputArgument::REQUIRED, '主题编码')
            ->setDescription('注册主题到EduSoho');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();

        $code = $input->getArgument('code');
        $output->writeln("<comment>注册主题`{$code}`：</comment>");

        $themeDir = dirname($this->getContainer()->getParameter('kernel.root_dir')).'/web/themes/'.$code;

        if (!is_dir($themeDir)) {
            throw new \RuntimeException($this->trans('主题目录%themeDir%不存在！', array('%themeDir%' => $themeDir)));
        }

        $output->writeln('<comment>  - 检查主题目录...</comment><info>OK</info>');

        $meta = $this->parseMeta($code, $themeDir);
        $output->writeln('<comment>  - 获取主题元信息...</comment><info>OK</info>');

        $meta['type'] = 'theme';
        $app = $this->getAppService()->registerApp($meta);
        $output->writeln('<comment>  - 添加应用记录...</comment><info>OK</info>');

        $this->initBlock($themeDir.'/block.json', $this->getContainer());
        $output->writeln('<comment>  - 插入编辑区元信息成功...</comment><info>OK</info>');

        PluginUtil::refresh();
        $output->writeln('<comment>  - 刷新主题缓存...</comment><info>OK</info>');
        $output->writeln('<info>注册成功....</info>');

        $theme = $meta;
        $theme['uri'] = $code;
        $this->getBiz()->service('Theme:ThemeService')->changeTheme($theme);
        $output->writeln('<info>应用主题成功...</info>');
    }

    private function parseMeta($code, $pluginDir)
    {
        $metaFile = $pluginDir.'/theme.json';

        if (!file_exists($metaFile)) {
            throw new \RuntimeException("插件元信息文件{$metaFile}不存在！");
        }

        $meta = json_decode(file_get_contents($metaFile), true);

        if (empty($meta)) {
            throw new \RuntimeException("插件元信息文件{$metaFile}格式不符合JSON规范，解析失败，请检查元信息文件格式");
        }

        if (empty($meta['code']) || empty($meta['name']) || empty($meta['version'])) {
            throw new \RuntimeException('插件元信息必须包含code、name、version属性');
        }

        if ($meta['code'] != $code) {
            throw new \RuntimeException("插件元信息code的值`{$meta['code']}`不正确，应为`{$code}`。");
        }

        return $meta;
    }

    private function initBlock($jsonFile, $container)
    {
        BlockToolkit::init($jsonFile, $container);
    }

    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    protected function getBlockService()
    {
        return $this->createService('Content:BlockService');
    }
}
