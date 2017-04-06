<?php
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class GracefulBlockMigrate extends AbstractMigrate
{
    public function update($page)
    {
        $gracefullBlockCount = $this->getConnection()->fetchColumn("
            SELECT count(id) from block_template where category = 'graceful';
        ");

        if (empty($gracefullBlockCount)) {
            return;
        }

        $this->getConnection()->exec("
            DELETE FROM block_template where category = 'graceful';
        ");

        $jsonFile = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../plugins/GracefulThemePlugin/block.json");

        if (!file_exists($jsonFile)) {
            return;
        }

        $blockMeta = json_decode(file_get_contents($jsonFile), true);

        if (empty($blockMeta)) {
            return;
        }

        foreach ($blockMeta as $key => $meta) {
            $default = array();
            foreach ($meta['items'] as $i => $item) {
                $default[$i] = $item['default'];
            }

            $blockTemplate = array(
                'code' => $key,
                'mode' => 'template',
                'category' => empty($meta['category']) ? 'system' : $meta['category'],
                'meta' => json_encode($meta),
                'data' => json_encode($default),
                'templateName' => $meta['templateName'],
                'title' => $meta['title'],
                'createdTime' => time()
            );

            $this->getConnection()->insert('block_template', $blockTemplate);
            $insertId = $this->getConnection()->lastInsertId();

            $blockTemplate = $this->getConnection()->fetchAssoc("SELECT * from block_template where id = {$insertId}");

            if (empty($blockTemplate['content'])) {
                $blockTemplate['meta'] = json_decode($blockTemplate['meta'], true);
                $blockTemplate['data'] = json_decode($blockTemplate['data'], true);

                global $kernel;

                $content = $this->render($blockTemplate, $kernel->getContainer());

                $updateFields = array(
                    'content' => $content,
                    'updateTime' => time(),
                );

                $this->getConnection()->update('block_template', $updateFields, array('id' => $blockTemplate['id']));
            }
        }
    }

    private function render($block, $container)
    {
        if (!$container->isScopeActive('request')) {
            $container->enterScope('request');
            $container->set('request', new Request(), 'request');
        }

        if (empty($block['templateName']) || empty($block['data'])) {
            return '';
        }

        return $container->get('templating')->render($block['templateName'], $block['data']);
    }
}
