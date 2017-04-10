<?php

class FileMigrate extends AbstractMigrate
{
    public function update($page)
    {
        if ($page == 27) {
            return 0;
        }

        if($page == 25){
            $this->extractUpgradeFiles();
            return $page + 1;
        }

        if($page == 26){
            $this->copyAndOverwriteUpgradeFiles();
            return $page + 1;
        }

        $filepath = 'http://ojc8jepus.bkt.clouddn.com/x8-package/x8-'.$page.'.zip';

        $dir = $this->kernel->getParameter('kernel.root_dir').'/data/upgrade/es-8.0';

        $filesystem = new \Symfony\Component\Filesystem\Filesystem();

        if (!$filesystem->exists($dir)) {
            $filesystem->mkdir($dir);
        }

        $targetPath = $dir.'/upgrade-'.$page.'.zip';
        touch($targetPath);
        file_put_contents($targetPath, file_get_contents($filepath));
        return $page + 1;
    }

    private function extractUpgradeFiles()
    {
        $filesystem = new \Symfony\Component\Filesystem\Filesystem();
        $tmpUnzipDir = $this->kernel->getParameter('kernel.root_dir').'/data/upgrade/es-8.0';

        foreach (range(1, 24) as $page) {
            $zip = new \ZipArchive;
            $filepath = $this->kernel->getParameter('kernel.root_dir').'/data/upgrade/es-8.0/upgrade-' . $page . '.zip';

            if ($zip->open($filepath) === true) {
                $zip->extractTo($tmpUnzipDir);
                $zip->close();
                $filesystem->remove($filepath);
            } else {
                throw new \Exception('无法解压缩安装包！');
            }
        }


    }

    private function copyAndOverwriteUpgradeFiles()
    {
        $tmpUnzipDir = $this->kernel->getParameter('kernel.root_dir').'/data/upgrade/es-8.0';
        $edusohoDir = $this->kernel->getParameter('kernel.root_dir') . '/../';

        $filesystem = new \Symfony\Component\Filesystem\Filesystem();
        $filesystem->mirror($tmpUnzipDir, $edusohoDir, null, array(
            'override' => true,
            'copy_on_windows' => true,
        ));
        $filesystem->remove($tmpUnzipDir);
    }
}