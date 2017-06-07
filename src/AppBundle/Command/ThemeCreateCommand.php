<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Topxia\Service\Common\ServiceKernel;
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
        $dir =  __DIR__ . '/../../../web/themes/';
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
        $this->createInstallScript();
        $this->createImg($dir, $name);

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
        $this->filesystem->mkdir($themeDir.'static-dist/'.$name.'theme');
        $this->filesystem->mkdir($themeDir.'views');
        $this->filesystem->mkdir($themeDir.'Scripts');
        $this->output->writeln('创建目录: <info>OK</info>');
    }

    private function createInstallScript()
    {
        $data = file_get_contents(__DIR__.'/theme-tpl/InstallScript.twig');
        file_put_contents($this->themeDir.'Scripts/InstallScript.php', $data);
        $this->output->writeln('创建安装脚本: <info>OK</info>');
    }

    private function createThemeJson($dir, $name)
    {
        $time = date("Y-m-d");
        $filename = $this->themeDir.'theme.json';
        $data =
'{
    "code": "'.$name.'",
    "name": "'.$name.'",
    "description": "",
    "author": "edusoho",
    "version": "1.0.0",
    "support_version": "8.0.0+",
    "date": "'. $time .'",
    "thumb": "theme.jpg",
    "protocol": "3"
}';

       file_put_contents($filename, $data);
       $this->output->writeln('创建theme.json: <info>OK</info>');
    }

    private function createImg($dir, $name)
    {
        $myImage = ImageCreate(500,320);

        $green = ImageColorAllocate($myImage, 70, 195, 123);
        $white = ImageColorAllocate($myImage, 255, 255, 255);

        imagettftext($myImage, 100, 0, 80, 200, $white, __DIR__.'/theme-tpl/OBLIVIOUSFONT.TTF',  "demo");
        ImagePng($myImage, $this->themeDir.'theme.jpg');
     }
}
