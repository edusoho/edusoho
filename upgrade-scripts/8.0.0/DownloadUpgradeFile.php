<?php

class DownloadUpgradeFile extends AbstractMigrate
{

    private $end = 46;
    private $start = 1;

    public function update($page)
    {

        if($page == $this->end + 1){
            $this->extractUpgradeFiles();
            return null;
        }

        $filepath = 'http://ojc8jepus.bkt.clouddn.com/x8-package/v7-'.$page.'.zip';

        $dir = $this->kernel->getParameter('kernel.root_dir').'/data/upgrade';

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

        foreach (range($this->start, $this->end) as $page) {
            $zip = new \ZipArchive;
            $filepath = $this->kernel->getParameter('kernel.root_dir').'/data/upgrade/upgrade-' . $page . '.zip';

            if ($zip->open($filepath) === true) {
                $zip->extractTo($tmpUnzipDir);
                $zip->close();
                $filesystem->remove($filepath);
            } else {
                throw new \Exception('无法解压缩安装包！');
            }
        }
    }
}
