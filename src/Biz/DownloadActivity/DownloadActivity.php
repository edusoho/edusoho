<?php
/**
 * User: Edusoho V8
 * Date: 02/11/2016
 * Time: 19:54
 */

namespace Biz\DownloadActivity;


use Biz\Activity\Config\Activity;
use Topxia\Common\ArrayToolkit;

class DownloadActivity extends Activity
{
    public function registerActions()
    {
        return array(
            'create' => 'WebBundle:DownLoadActivity:create',
            'edit'   => 'WebBundle:DownLoadActivity:edit',
            'show'   => 'WebBundle:DownLoadActivity:show',
        );
    }

    protected function registerListeners()
    {
        // TODO: Implement registerListeners() method.
    }

    public function getMetas()
    {
        return array(
            'name' => '下载资料',
            'icon' => 'es-icon es-icon-filedownload'
        );
    }


    /**
     * @inheritdoc
     */
    public function create($fields)
    {
        $materials = json_decode($fields['materials'], true);
        $that      = $this;
        $ext       = $this->getConnection()->transactional(function () use ($materials, $that) {
            //1. created ext
            $downloadExtension = $this->parseDownloadExtension($materials);
            $downloadExtension = $this->getDownloadActivityDao()->create($downloadExtension);
            //2. created file
            $files = $this->parseDownloadFiles($downloadExtension, $materials);
            foreach ($files as $file) {
                $that->getDownloadFileDao()->create($file);
            }
            return $downloadExtension;
        });
        return $ext;
    }

    /**
     * @inheritdoc
     */
    public function update($id, $fields)
    {
        $ext = $this->parseDownloadActivityExt($fields);
        $ext = $this->getDownloadActivityDao()->update($id, $ext);
        return $ext;
    }

    /**
     * @inheritdoc
     */
    public function delete($id)
    {
        return $this->getDownloadActivityDao()->delete($id);
    }

    /**
     * @inheritdoc
     */
    public function get($id)
    {
        $downloadActivity               = $this->getDownloadActivityDao()->get($id);
        $downloadActivity['materials'] = $this->getDownloadFileDao()->findFilesByDownloadActivityId($downloadActivity['id']);
        return $downloadActivity;
    }

    protected function parseDownloadExtension($materials)
    {
        return array('mediaCount' => count($materials));
    }

    protected function parseDownloadFiles($downloadExtension, $materials)
    {
        $files = array();
        $extId = $downloadExtension['id'];
        array_walk($materials, function ($material) use ($extId, &$files) {
            $file = array(
                'downloadActivityId' => $extId,
                'title'              => $material['name'],
                'fileId'             => intval($material['id']),
                'fileSize'           => $material['size'],
            );
            if ($material['source'] == "link") {
                $file['link'] = $material['link'];
            }
            $files[] = $file;
        });
        return $files;
    }

    protected function parseDownloadActivityExt($fields)
    {
        $ext = array();

        var_dump($fields);
        $materials  = ArrayToolkit::index($fields, 'id');
        $fileMedias = array_filter(array_keys($materials), function ($id) {
            return $id > 0;
        });

        $ext['fileMediaCount'] = count($fileMedias);
        $ext['mediaCount']     = count(array_keys($materials));
        $ext['media']          = $materials;//array_reverse($materials);

        array_walk($materials, function ($material, $key) use (&$ext) {
            if (empty(intval($material['id']))) {
                $ext['linkMedias'][] = $material['link'];
            } else {
                $ext['fileMediaIds'][] = $material['id'];
            }
        });

        return $ext;

    }

    protected function getDownloadActivityDao()
    {
        return $this->getBiz()->dao('DownloadActivity:DownloadActivityDao');
    }

    protected function getDownloadFileDao()
    {
        return $this->getBiz()->dao('DownloadActivity:DownloadFileDao');
    }

    protected function getConnection()
    {
        return $this->getBiz()->offsetGet('db');
    }


}