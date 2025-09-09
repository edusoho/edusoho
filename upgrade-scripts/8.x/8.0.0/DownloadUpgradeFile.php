<?php

class DownloadUpgradeFile extends AbstractMigrate
{
    private $end = 46;
    private $file_download_rate_file = 'file_download_rate.txt';

    public function update($page)
    {
        $dir = $this->kernel->getParameter('kernel.root_dir') . '/data/upgrade';
        $filesystem = new \Symfony\Component\Filesystem\Filesystem();
        $this->generateFile($filesystem, $dir);

        if ($page == $this->end + 1) {
            $this->copyNoneSideEffectFiles();
            @unlink($dir . '/' . $this->file_download_rate_file);
            return 0;
        }
        $page = $this->getPage($page, $dir);
        $targetPath = $this->getTargetPath($page, $dir);
        $url = 'http://download-devtest.codeages.net/x8-package/v11-' . $page . '.zip';
        file_put_contents($targetPath, file_get_contents($url));

        $zip = new \ZipArchive;
        $tmpUnzipDir = $this->kernel->getParameter('kernel.root_dir') . '/data/upgrade/es-8.0';
        if ($zip->open($targetPath) === true) {
            $this->logger('8.0.0', 'warning', 'download file from :' . $url = 'http://download-devtest.codeages.net/x8-package/v11-' . $page . '.zip');
            $this->logger('8.0.0', 'warning', $targetPath . ' file size is :' . filesize($targetPath));
            $zip->extractTo($tmpUnzipDir);
            $zip->close();
            file_put_contents($dir . '/' . $this->file_download_rate_file, $page);
        } else {
            throw new \Exception('无法解压缩安装包！');
        }

        return $page + 1;
    }

    private function copyNoneSideEffectFiles()
    {
        $sourceDir = $this->kernel->getParameter('kernel.root_dir') . '/data/upgrade/es-8.0/source';
        $edusohoDir = $this->kernel->getParameter('kernel.root_dir') . '/../';

        $filesystem = new \Symfony\Component\Filesystem\Filesystem();
        $this->logger('8.0.0', 'warning',  ' copyNoneSideEffectFiles copy /src/AppBundle');
        // copy or overwrite AppBundle Dir
        $filesystem->mirror($sourceDir . '/src/AppBundle', $edusohoDir . '/src/AppBundle', null, array(
            'override' => true,
            'copy_on_windows' => true,
        ));
        $this->logger('8.0.0', 'warning',  ' copyNoneSideEffectFiles copy /src/Biz');
        // copy or overwrite Biz Dir
        $filesystem->mirror($sourceDir . '/src/Biz', $edusohoDir . '/src/Biz', null, array(
            'override' => true,
            'copy_on_windows' => true,
        ));

        $this->logger('8.0.0', 'warning',  ' copyNoneSideEffectFiles copy /web/static-dist/');
        foreach (array('app', 'autumntheme', 'defaultbtheme', 'jianmotheme', 'defaulttheme', 'libs') as $dir) {
            $filesystem->mirror($sourceDir . '/web/static-dist/' . $dir, $edusohoDir . '/web/static-dist/' . $dir, null, array(
                'override' => true,
                'delete' => true,
                'copy_on_windows' => true,
            ));
        }
        $this->logger('8.0.0', 'warning',  ' copyNoneSideEffectFiles copy /app/Resources/static-src');
        $filesystem->mirror($sourceDir . '/app/Resources/static-src', $edusohoDir . '/app/Resources/static-src', null, array(
            'override' => true,
            'copy_on_windows' => true,
        ));
    }

    /**
     * @param $filesystem
     * @param $dir
     */
    protected function generateFile($filesystem, $dir)
    {
        if (!$filesystem->exists($dir)) {
            $filesystem->mkdir($dir);
        }
        $cacheRateFile = $dir . '/' . $this->file_download_rate_file;
        if (!file_exists($cacheRateFile)) {
            $filesystem->touch($cacheRateFile);
        }
    }

    /**
     * @param $page
     * @param $dir
     * @return int
     */
    protected function getPage($page, $dir)
    {
        $cachedRate = file_get_contents($dir . '/' . $this->file_download_rate_file);
        $page = ((int)$cachedRate > $page) ? ((int)$cachedRate) : $page;
        return $page;
    }

    /**
     * @param $page
     * @param $dir
     * @return string
     */
    protected function getTargetPath($page, $dir)
    {
        $targetPath = $dir . '/upgrade-' . $page . '.zip';
        touch($targetPath);
        return $targetPath;
    }
}
