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
        $ext = $this->parseDownloadActivityExt($fields);
        $ext = $this->getDownloadActivityDao()->create($ext);
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
        return $this->getDownloadActivityDao()->get($id);
    }

    protected function parseDownloadActivityExt($fields)
    {
        $ext        = array();
        $materials  = json_decode($fields['materials'], true);
        $materials  = ArrayToolkit::index($materials, 'id');
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


}