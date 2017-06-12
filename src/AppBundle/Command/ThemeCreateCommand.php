<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Filesystem\Filesystem;

class ThemeCreateCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->addArgument(
                'themename',
                InputArgument::OPTIONAL,
                '主题名称?'
            )
            ->setName('theme:create')
            ->setDescription('创建主题模板');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('themename');
        $dir = __DIR__.'/../../../web/themes/';
        $filename = $dir.'/theme.json';
        $this->filesystem = new Filesystem();
        $this->output = $output;
        $this->themeDir = $dir.$name.'/';

        if (!$name) {
            throw new \RuntimeException('主题名称不能为空！');
        }

        if (!preg_match('/^[a-zA-Z\s]+$/', $name)) {
            throw new \RuntimeException('主题名称只能为英文！');
        }

        if ($this->filesystem->exists($dir.$name)) {
            throw new \RuntimeException('主题已经存在了');
        }

        $this->filesystem->mkdir($dir.$name);
        $this->createThemeJson($dir, $name, $output);
        $this->createOtherDirectories($name);
        $this->createInstallScript($name);
        $this->createImg($dir, $name);
        $this->createJs($name);
        $this->createLess($name);
        $this->createIndexView($name);
        $this->createCssView($name);
        $this->createBlock($name);
        $output->writeln('创建主题包: <info>OK</info>');
    }

    private function createOtherDirectories($name)
    {
        $themeDir = $this->themeDir;
        $this->filesystem->mkdir($themeDir.'block');
        $this->filesystem->mkdir($themeDir.'static-dist');
        $this->filesystem->mkdir($themeDir.'static-src/less');
        $this->filesystem->mkdir($themeDir.'static-src/js');
        $this->filesystem->mkdir($themeDir.'static-src/font');
        $this->filesystem->mkdir($themeDir.'static-src/img');
        $this->filesystem->mkdir($themeDir.'static-dist/'.$name.'theme/css');
        $this->filesystem->mkdir($themeDir.'static-dist/'.$name.'theme/js');
        $this->filesystem->mkdir($themeDir.'views/default');
        $this->filesystem->mkdir($themeDir.'Scripts');
        $this->output->writeln('创建目录: <info>OK</info>');
    }

    private function createInstallScript($name)
    {
        $data = file_get_contents(__DIR__.'/theme-tpl/InstallScript.twig');
        $data = str_replace('{{name}}', $name, $data);
        file_put_contents($this->themeDir.'Scripts/InstallScript.php', $data);
        $this->output->writeln('创建安装脚本: <info>OK</info>');
    }

    private function createThemeJson($dir, $name)
    {
        $time = date('Y-m-d');
        $filename = $this->themeDir.'theme.json';
        $data =
'{
    "code": "'.$name.'",
    "name": "'.$name.'",
    "description": "",
    "author": "edusoho",
    "version": "1.0.0",
    "support_version": "8.0.0+",
    "date": "'.$time.'",
    "thumb": "theme.jpg",
    "protocol": "3"
}';

        file_put_contents($filename, $data);
        $this->output->writeln('创建theme.json: <info>OK</info>');
    }

    private function createImg($dir, $name)
    {
        $imgWidth = 500;
        $imgHeight = 320;

        $myImage = imagecreate($imgWidth, $imgHeight);

        $green = imagecolorallocate($myImage, 70, 195, 123);
        $white = imagecolorallocate($myImage, 255, 255, 255);
        $size = 100 - (strlen($name) - 4) * 10;
        if ($size < 10) {
            $size = 10;
        }
        $x = 70 - (strlen($name) - 4) * 10;

        imagettftext($myImage, $size, 0, $x, 200, $white, __DIR__.'/theme-tpl/OBLIVIOUSFONT.TTF', $name);
        imagepng($myImage, $this->themeDir.'theme.jpg');
        $this->output->writeln('创建 图片: <info>OK</info>');
    }

    private function createJs($name)
    {
        $data = file_get_contents(__DIR__.'/theme-tpl/mainjs.twig');
        $data1 = file_get_contents(__DIR__.'/theme-tpl/main.js');
        file_put_contents($this->themeDir.'static-src/js/main.js', $data);
        file_put_contents($this->themeDir."static-dist/{$name}theme/js/main.js", $data1);
        $this->output->writeln('创建js: <info>OK</info>');
    }

    private function createLess($name)
    {
        $data = file_get_contents(__DIR__.'/theme-tpl/themeless.twig');
        file_put_contents($this->themeDir.'static-src/less/theme.less', $data);
        file_put_contents($this->themeDir."static-dist/{$name}theme/css/theme.css", $data);
        $this->output->writeln('创建less: <info>OK</info>');
    }

    private function createIndexView($name)
    {
        $data = file_get_contents(__DIR__.'/theme-tpl/index.twig');
        $data = str_replace('{{name}}', $name, $data);
        file_put_contents($this->themeDir.'views/default/index.html.twig', $data);
        $this->output->writeln('创建主题首页模板: <info>OK</info>');
    }

    private function createCssView($name)
    {
        $data = file_get_contents(__DIR__.'/theme-tpl/webpackcss.twig');
        $data = str_replace('{{name}}', $name, $data);
        file_put_contents($this->themeDir.'views/default/stylesheet-webpack.html.twig', $data);
        $this->output->writeln('重新样式加载文件: <info>OK</info>');
    }

    private function createBlock($name)
    {
        $data = file_get_contents(__DIR__.'/theme-tpl/block.json');
        $data = str_replace('{{name}}', $name, $data);
        file_put_contents($this->themeDir.'block.json', $data);

        $data = file_get_contents(__DIR__.'/theme-tpl/block-tpl.twig');
        $data = str_replace('{{name}}', $name, $data);
        file_put_contents($this->themeDir.'block/carousel.template.html.twig', $data);

        $this->output->writeln('创建编辑区: <info>OK</info>');
    }
}
