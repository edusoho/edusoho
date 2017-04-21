<?php

class DownloadUpgradeFile extends AbstractMigrate
{

    private $end = 46;
    private $start = 1;

    public function update($page)
    {
        if($page == $this->end + 1){
            $this->copyNoneSideEffectFiles();
            return 0;
        }

        $url = 'http://ojc8jepus.bkt.clouddn.com/x8-package/v10-'.$page.'.zip';

        $dir = $this->kernel->getParameter('kernel.root_dir').'/data/upgrade';

        $filesystem = new \Symfony\Component\Filesystem\Filesystem();

        if (!$filesystem->exists($dir)) {
            $filesystem->mkdir($dir);
        }

        $targetPath = $dir.'/upgrade-'.$page.'.zip';
        touch($targetPath);
        file_put_contents($targetPath, file_get_contents($url));

        $zip = new \ZipArchive;

        $tmpUnzipDir = $this->kernel->getParameter('kernel.root_dir').'/data/upgrade/es-8.0';
        if ($zip->open($targetPath) === true) {
            $zip->extractTo($tmpUnzipDir);
            $zip->close();
            $filesystem->remove($targetPath);
        } else {
            throw new \Exception('无法解压缩安装包！');
        }

        return $page + 1;
    }

    private function copyNoneSideEffectFiles()
    {
        $sourceDir = $this->kernel->getParameter('kernel.root_dir').'/data/upgrade/es-8.0/source';
        $edusohoDir = $this->kernel->getParameter('kernel.root_dir') . '/../';

        $filesystem = new \Symfony\Component\Filesystem\Filesystem();

        // copy or overwrite AppBundle Dir
        $filesystem->mirror($sourceDir.'/src/AppBundle', $edusohoDir.'/src/AppBundle', null, array(
            'override' => true,
            'copy_on_windows' => true,
        ));

        // copy or overwrite Biz Dir
        $filesystem->mirror($sourceDir.'/src/Biz', $edusohoDir.'/src/Biz', null, array(
            'override' => true,
            'copy_on_windows' => true,
        ));

        foreach (array('app', 'autumntheme', 'defaultbtheme', 'jianmotheme', 'defaulttheme', 'libs') as $dir){
            $filesystem->mirror($sourceDir.'/web/static-dist/'.$dir, $edusohoDir.'/web/static-dist/'.$dir, null, array(
                'override' => true,
                'delete' => true,
                'copy_on_windows' => true,
            ));
        }

        $filesystem->mirror($sourceDir.'/app/Resources/static-src', $edusohoDir.'/app/Resources/static-src', null, array(
            'override' => true,
            'copy_on_windows' => true,
        ));
    }
}
