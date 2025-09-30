class DataCollectApp
{
    const PACKAGEID = 1408;
    const LATEST_VERSION_ERROR = 1111;

    private $mainVersion;
    private $package;

    public function __construct($container)
    {
        $this->container = $container;
        $this->client = $this->getClient();
    }

    public function getDb()
    {
        try {
            $biz = $this->container->get("biz");
            return $biz["db"];
        } catch (\Exception $e) {
            return $this->container->get("database_connection");
        }
    }

    private function getByCode($code)
    {
        $data = $this->getDb()->fetchAssoc("SELECT * FROM `cloud_app` where code = \"{$code}\"");
        if (empty($data)) {
            return array();
        }

        return $data;
    }

    private function getSettingByName($name)
    {
        $data = $this->getDb()->fetchAssoc("SELECT * FROM `setting` where name = \"{$name}\"");
        if (empty($data)) {
            return array();
        }

        return unserialize($data["value"]);
    }

    public function exec()
    {
        $main = $this->getByCode("MAIN");
        $mainVersion = $main["version"];
        $this->mainVersion = $mainVersion;
        $this->addOrderIndex($mainVersion);
        $collectPlugin = $this->getPluginApp();
        $info = $this->downloadPlugin($collectPlugin);

        if (isset($info["status"]) && $info["status"] === "error") {
            throw new \Exception($info["errors"]);
        }

        list($filepath, $packageId) = $info;
        $package = $this->client->getPackage($packageId);
        $this->package = $package;

        $unzipResult = $this->unzipPackageFile($filepath, $this->makePackageFileUnzipDir($package));
        if (true !== $unzipResult) {
            throw new \Exception("无法解压压缩包：".$unzipResult);
        }

        $this->movePlugin($package, $mainVersion);

        if (empty($collectPlugin)) {
            $this->createApp($package, $mainVersion);
        } else {
            $this->updateApp($collectPlugin["id"], $mainVersion, $package);
        }

        $this->refreshPlugin();
        $this->deleteCache();
        return $this->makeSuccessResult();
    }

    public function makeSuccessResult()
    {
        return array(
            "status" => "success",
            "message" => "成功",
            "mainVersion" => $this->mainVersion,
            "plugin" => $this->package,
        );
    }

    private function addOrderIndex($mainVersion)
    {
        if (version_compare($mainVersion, "8.2.0") >= 0) {
            if (!$this->isIndexExist("biz_order", "created_time")) {
                $this->getDb()->exec("
                    ALTER TABLE biz_order ADD INDEX idx_created_time (`created_time`);
                ");             
            }
        } else {
            if (!$this->isIndexExist("orders", "createdTime")) {
                $this->getDb()->exec("
                    ALTER TABLE orders ADD INDEX idx_created_time (`createdTime`);
                ");
            }
        }
    }

    private function isIndexExist($table, $filedName)
    {
        $sql = "show index from `{$table}` where column_name = \"{$filedName}\";";
        $result = $this->getDb()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    private function refreshPlugin()
    {
        if (class_exists("\Biz\Util\PluginUtil")) {
            \Biz\Util\PluginUtil::refresh();
            return;
        }

        \Topxia\Service\Util\PluginUtil::refresh();
    }

    private function createApp($package, $mainVersion)
    {
        $pluginCode = $this->getPluginCode($mainVersion);
        $newApp = array(
            "code"          => $pluginCode,
            "name"          => $package["product"]["name"],
            "description"   => $package["product"]["description"],
            "icon"          => $package["product"]["icon"],
            "version"       => $package["toVersion"],
            "fromVersion"   => $package["fromVersion"],
            "developerId"   => $package["product"]["developerId"],
            "developerName" => $package["product"]["developerName"],
            "updatedTime"   => time(),
            "type" => "plugin",
            "installedTime" => time(),
        );
        if (version_compare($mainVersion, "8.0.0") >= 0) {
            $newApp["protocol"] = 3;
        }

        $this->getDb()->insert("cloud_app", $newApp);
    }

    private function updateApp($id, $mainVersion, $package)
    {
        $fields = array(
            "version" => $package["toVersion"],
            "fromVersion" => $package["fromVersion"],
            "code" => $this->getPluginCode($mainVersion)
        );

        if (version_compare($mainVersion, "8.0.0") >= 0) {
            $newApp["protocol"] = 3;
        }

        $this->getDb()->update("cloud_app", $fields, array("id" => $id));
    }

    protected function makePackageFileUnzipDir($package)
    {
        return $this->container->getParameter("topxia.disk.update_dir").DIRECTORY_SEPARATOR.$package["fileName"];
    }

    protected function unzipPackageFile($source, $destination)
    {
        $filesystem = new \Symfony\Component\Filesystem\Filesystem();

        if ($filesystem->exists($destination)) {
            $filesystem->remove($destination);
        }

        $tmpUnzipDir = $destination."_tmp";

        if ($filesystem->exists($tmpUnzipDir)) {
            $filesystem->remove($tmpUnzipDir);
        }

        $filesystem->mkdir($tmpUnzipDir);

        $zip = new \ZipArchive();
        $res = $zip->open($source);
        if (true === $res) {
            $tmpUnzipFullDir = $tmpUnzipDir."/".$zip->getNameIndex(0);
            $zip->extractTo($tmpUnzipDir);
            $zip->close();
            $filesystem->rename($tmpUnzipFullDir, $destination);
            $filesystem->remove($tmpUnzipDir);
        }

        return $res;
    }

    public function downloadPlugin($app)
    {
        $packageId = self::PACKAGEID;
        $extInfos = array("_t" => (string) time());
        if (!empty($app)) {
            $info["DataCollect"] = $app["version"];
            $apps = $this->client->checkUpgradePackages($info, $extInfos);
            if (empty($apps)) {
                throw new \Exception("Maybe your version is latest or packages not found", self::LATEST_VERSION_ERROR);
            }

            $packageId = $apps[0]["latestPackageId"];
        } else {
            $apps = $this->client->checkUpgradePackages(array(
                "DataCollect" => "0.0.0",
            ), $extInfos);
            if (!empty($apps[0])) {
                $packageId = $apps[0]["latestPackageId"];
            }
        }

        return array($this->client->downloadPackage($packageId), $packageId);
    }

    public function getPluginApp()
    {
        $app = $this->getByCode("DataCollect");
        if (empty($app)) {
            $app = $this->getByCode("DataCollectPlugin");
        }

        return $app;
    }

    private function movePlugin($package, $mainVersion)
    {
        $fileName = $package["fileName"];
        $originPath = $this->container->getParameter("topxia.disk.update_dir")."/{$fileName}/source";
        $filesystem = new \Symfony\Component\Filesystem\Filesystem();
        $targetPath = $this->getTargetPath();
        $filesystem->mirror($originPath, $targetPath, null, array(
            "override" => true,
            "copy_on_windows" => true,
        ));

        if (version_compare($mainVersion, "8.0.0") < 0) {
            $this->changePluginCode();
        }
    }

    private function changePluginCode()
    {
        $targetPath = $this->getTargetPath();
        $jsonPath = $targetPath."/DataCollectPlugin/plugin.json";

        $data = file_get_contents($jsonPath);
        $data = json_decode($data,true);
        $data["code"] = "DataCollectPlugin";

        $jsonStrings = json_encode($data);
        file_put_contents($jsonPath, $jsonStrings);
    }

    private function getClient()
    {
        $cloud = $this->getSettingByName("storage");
        $developer = $this->getSettingByName("developer");

        $options = array(
            "accessKey" => empty($cloud["cloud_access_key"]) ? null : $cloud["cloud_access_key"],
            "secretKey" => empty($cloud["cloud_secret_key"]) ? null : $cloud["cloud_secret_key"],
            "apiUrl" => empty($developer["app_api_url"]) ? null : $developer["app_api_url"],
        );

        if (class_exists("\Biz\CloudPlatform\Client\EduSohoAppClient")) {
            return new \Biz\CloudPlatform\Client\EduSohoAppClient($options);
        }

        return new Topxia\Service\CloudPlatform\Client\EduSohoAppClient($options);
    }

    private function getTargetPath()
    {
        return $this->container->getParameter("kernel.root_dir")."/../plugins";
    }

    private function getPluginCode($mainVersion)
    {
        return version_compare($mainVersion, "8.0.0") < 0 ? "DataCollectPlugin" : "DataCollect";
    }

    private function deleteCache($tryCount = 1)
    {
        if ($tryCount >= 5) {
            throw new \Exception("cannot delete cache.");
        }

        sleep($tryCount * 2);

        try {
            $cachePath = dirname($this->container->getParameter("kernel.cache_dir"));
            $filesystem = new \Symfony\Component\Filesystem\Filesystem();
            $filesystem->remove($cachePath);
            
            clearstatcache(true);

            if (!$filesystem->exists($cachePath."/annotations/topxia")) {
                $filesystem->mkdir($cachePath."/annotations/topxia");
            }
        } catch (\Exception $e) {
            ++$tryCount;
            $this->deleteCache($tryCount);
        }
    }
}

try {
    $app = new DataCollectApp($this->container);
    echo json_encode($app->exec());
} catch (\Exception $e) {
    if ($e->getCode() == DataCollectApp::LATEST_VERSION_ERROR) {
      echo json_encode($app->makeSuccessResult());
    } else {
        echo json_encode(array(
            "status" => "error",
            "message" => $e->getMessage(),
        ));
    }
}