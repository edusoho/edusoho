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

        $this->createImg($dir, $name);
        $this->createPostImg($dir, $name);
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
        $themeDirs = array(
            'block',
            'static-dist',
            'static-src/js/index',
            'static-src/less',
            'static-src/font',
            'static-src/img',
            'views/default',
            'views/stylesheet',
        );
        foreach ($themeDirs as $value) {
            $this->filesystem->mkdir($themeDir.$value);
        }

        $this->output->writeln('创建目录: <info>OK</info>');
    }

    private function createThemeJson($dir, $name)
    {
        $time = date('Y-m-d');
        $filename = $this->themeDir.'theme.json';
        $data = file_get_contents(__DIR__ . '/theme-tpl/theme.json');
        $data = str_replace('{{name}}', $name, $data);
        $data = str_replace('{{time}}', $time, $data);

        file_put_contents($filename, $data);
        $this->output->writeln('创建theme.json: <info>OK</info>');
    }

    private function createPostImg()
    {
        $imgWidth = 1920;
        $imgHeight = 500;
        $myImage = imagecreate($imgWidth, $imgHeight);
        $green = imagecolorallocate($myImage, 70, 195, 123);
        $white = imagecolorallocate($myImage, 255, 255, 255);
        imagettftext($myImage, 100, 0, 550, 320, $white, __DIR__.'/theme-tpl/OBLIVIOUSFONT.TTF', 'hello world');
        imagepng($myImage, $this->themeDir.'static-src/img/post1.jpg');

        $myImage = imagecreate($imgWidth, $imgHeight);
        $bule = imagecolorallocate($myImage, 136, 167, 255);
        $white = imagecolorallocate($myImage, 255, 255, 255);
        imagettftext($myImage, 100, 0, 550, 320, $white, __DIR__.'/theme-tpl/OBLIVIOUSFONT.TTF', 'hello world');
        imagepng($myImage, $this->themeDir.'static-src/img/post2.jpg');

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
        $data = file_get_contents(__DIR__.'/theme-tpl/js/src-main.js');
        $data1 = file_get_contents(__DIR__.'/theme-tpl/js/src-index.js');
        file_put_contents($this->themeDir.'static-src/js/main.js', $data);
        file_put_contents($this->themeDir.'static-src/js/index/index.js', $data1);
        $this->output->writeln('创建js: <info>OK</info>');
    }

    private function createLess($name)
    {
        $data = file_get_contents(__DIR__.'/theme-tpl/theme.less');
        file_put_contents($this->themeDir.'static-src/less/main.less', $data);
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
        $data = file_get_contents(__DIR__ . '/theme-tpl/stylesheet-custom.html.twig');
        $data = str_replace('{{name}}', $name, $data);
        file_put_contents($this->themeDir.'views/stylesheet/stylesheet-custom.html.twig', $data);
        $this->output->writeln('重新样式加载文件: <info>OK</info>');
    }

    private function createBlock($name)
    {
        $data = file_get_contents(__DIR__.'/theme-tpl/block.json');
        $data = str_replace('{{name}}', $name, $data);
        file_put_contents($this->themeDir.'block.json', $data);

        $data = file_get_contents(__DIR__.'/theme-tpl/block-tpl.twig');
        $data = str_replace('{{name}}', $name, $data);
        file_put_contents($this->themeDir.'block/carousel.html.twig', $data);

        $this->output->writeln('创建编辑区: <info>OK</info>');
    }

    private function createParameter($name)
    {
        $data = file_get_contents(__DIR__.'/theme-tpl/parameter.json');
        $data = str_replace('{{name}}', $name, $data);
        file_put_contents($this->themeDir.'config/parameter.json', $data);

        $this->output->writeln('创建挂件配置: <info>OK</info>');
    }
}
