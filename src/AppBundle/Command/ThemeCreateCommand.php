<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Filesystem\Filesystem;

class ThemeCreateCommand extends BaseCommand
{
    private $filesystem;

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

        $this->filesystem->mirror(__DIR__.'/ThemeTemplate/', $this->themeDir, null, array(
            'copy_on_windows' => true,
        ));

        $this->updateThemeJson($name);
        $this->createImg($name);
        $this->updateIndexView($name);
        $this->updateCssView($name);
        $this->updateBlock($name);
        $this->deleteUselessFiles();

        $output->writeln('创建主题包: <info>OK</info>');
    }

    private function updateThemeJson($name)
    {
        $time = date('Y-m-d');
        $filename = $this->themeDir.'theme.json';
        $data = file_get_contents($filename);
        $data = str_replace('{{name}}', $name, $data);
        $data = str_replace('{{time}}', $time, $data);

        file_put_contents($filename, $data);
    }

    private function createImg($name)
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

        imagettftext($myImage, $size, 0, $x, 200, $white, __DIR__.'/ThemeTemplate/static-src/font/OBLIVIOUSFONT.TTF', $name);
        imagepng($myImage, $this->themeDir.'theme.jpg');
    }

    private function updateIndexView($name)
    {
        $data = file_get_contents(__DIR__.'/ThemeTemplate/views/default/index.html.twig');
        $data = str_replace('{{name}}', $name, $data);
        file_put_contents($this->themeDir.'views/default/index.html.twig', $data);
    }

    private function updateCssView($name)
    {
        $data = file_get_contents($this->themeDir.'views/stylesheet/stylesheet-custom.html.twig');
        $data = str_replace('{{name}}', $name, $data);
        file_put_contents($this->themeDir.'views/stylesheet/stylesheet-custom.html.twig', $data);
    }

    private function updateBlock($name)
    {
        $data = file_get_contents($this->themeDir.'block.json');
        $data = str_replace('{{name}}', $name, $data);
        file_put_contents($this->themeDir.'block.json', $data);

        $data = file_get_contents(__DIR__.'/ThemeTemplate/block/carousel.html.twig');
        $data = str_replace('{{name}}', $name, $data);
        file_put_contents($this->themeDir.'block/carousel.html.twig', $data);
    }

    private function deleteUselessFiles()
    {
        $this->filesystem->remove($this->themeDir.'parameter.json');
        $this->filesystem->remove($this->themeDir.'static-src/font/OBLIVIOUSFONT.TTF');
    }
}
