<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixCdnUrlInDatabaseCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('fix:cdn-url-in-database')
            ->addArgument('module', InputArgument::REQUIRED, '模块')
            ->addOption(
                'real',
                InputArgument::OPTIONAL
            )
            ->setDescription('修复 CDN 关闭后，一些业务模块数据库总任然存着的 CDN 地址。模块有（question: 题库模块）');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $availableModules = ['question'];
        $module = $input->getArgument('module');
        $real = $input->getOption('real');

        if (!in_array($module, $availableModules)) {
            echo "module 参数不正确！";
            return ;
        }

        if ($module == 'question') {
            $this->fixQuestionModule($real);
        }

    }

    private function fixQuestionModule($real = false)
    {
        $db = $this->getBiz()['db'];

        $sql = "SELECT COUNT(id) FROM biz_answer_question_report";
        $count = $db->fetchColumn($sql, [], 0);
        echo "biz_answer_question_report 表总共有 ${count} 条数据。\n";

        $cursor = 0;
        $limit = 100;
        $replaceCount = 0;
        while ( true) {
            if ($cursor > 0) {
                $sql = "SELECT * FROM biz_answer_question_report WHERE id < {$cursor} ORDER BY id DESC LIMIT ${limit}";
            } else {
                $sql = "SELECT * FROM biz_answer_question_report ORDER BY id DESC LIMIT ${limit}";
            }
            $rows = $db->fetchAll($sql);
            $count = count($rows);
            echo "查询到 {$count} 条数据，游标： ${cursor} \n";
            foreach ($rows as $row) {
                $cursor = $row['id'];
                echo "ID: {$row['id']} {$row['response']} \n";
                $pattern = '/<img.*?src=\\\"(http.*?-sb-qn\.qiqiuyun\.net)/i';
                if (preg_match_all($pattern, $row['response'], $matches)) {
                    echo "匹配到CDN地址：\n";
                    var_dump($matches[1]);

                    $replaced = $row['response'];
                    foreach ($matches[1] as $match) {
                        $replaced = str_replace($match, '', $replaced);
                    }
                    echo "替换后的结果：\n";
                    var_dump($replaced);

                    if ($real) {
                        echo "更新数据 Start.\n";
                        $db->update('biz_answer_question_report', array('response' => $replaced), array('id' => $row['id']));
                        echo "更新数据 End.\n";
                        echo "=================================================================";
                    }

                    $replaceCount ++;
                }
            }

            if (count($rows) < $limit) {
                echo "已处理完毕！\n";
                echo "共处理替换了 ${replaceCount} 条数据。\n";
                break;
            }
        }
    }
}