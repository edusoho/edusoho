<?php

namespace Codeages\Biz\ItemBank\ItemBank\Command;

use Codeages\Biz\Framework\Context\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class TableCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('item-bank:table')
            ->setDescription('Create a migration for the item-bank database table')
            ->addArgument('directory', InputArgument::REQUIRED, 'Migration base directory.', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = $input->getArgument('directory');

        $migrations = array(
            'init_item_bank',
            'add_table_biz_question_favorite',
            'add_table_biz_item_attachment',
            'add_table_biz_answer_scene_question_report',
            'add_index_identify_biz_answer_question_report',
            'add_index_answer_scene_id_biz_answer_report',
            'add_index_seq_biz_assessment_section_item',
            'alter_item_bank_add_question_num',
            'alter_item_category_add_question_num_item_num',
            'alter_answer_scene_add_doing_look_analysis',
            'add_index_assessmentId_seq_biz_assessment_section_item',
            'answer_scene_add_update_sometimes',
        );

        foreach ($migrations as $migration) {
            $this->copyNextMigration($directory, $migration);
        }

        $output->writeln('<info>Migration created successfully!</info>');
    }

    protected function copyNextMigration($directory, $next)
    {
        if (!$this->existMigration($directory, $next)) {
            $this->generateMigration($directory, 'biz_'.$next, __DIR__."/stub/{$next}.migration.stub");
        }
    }
}
