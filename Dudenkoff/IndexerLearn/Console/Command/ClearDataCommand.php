<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * CLI Command to clear test data
 */

namespace Dudenkoff\IndexerLearn\Console\Command;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ClearDataCommand extends Command
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     * @param string|null $name
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        string $name = null
    ) {
        parent::__construct($name);
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName('dudenkoff:indexer:clear-data');
        $this->setDescription('Clear all product statistics data (source and index tables)');
        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'Force deletion without confirmation'
        );

        parent::configure();
    }

    /**
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$input->getOption('force')) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(
                '<question>This will delete ALL data from source and index tables. Continue? (y/N)</question> ',
                false
            );

            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('<comment>Operation cancelled</comment>');
                return Cli::RETURN_SUCCESS;
            }
        }

        $connection = $this->resourceConnection->getConnection();
        $sourceTable = $this->resourceConnection->getTableName('dudenkoff_product_stats');
        $indexTable = $this->resourceConnection->getTableName('dudenkoff_product_stats_idx');

        try {
            // Get counts before deletion
            $sourceCount = $connection->fetchOne("SELECT COUNT(*) FROM {$sourceTable}");
            $indexCount = $connection->fetchOne("SELECT COUNT(*) FROM {$indexTable}");

            // Truncate both tables
            $connection->truncateTable($sourceTable);
            $connection->truncateTable($indexTable);

            $output->writeln('');
            $output->writeln('<info>✓ Data cleared successfully!</info>');
            $output->writeln('  - Source table: ' . $sourceCount . ' records deleted');
            $output->writeln('  - Index table:  ' . $indexCount . ' records deleted');

            return Cli::RETURN_SUCCESS;

        } catch (\Exception $e) {
            $output->writeln('<error>Error: ' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }
    }
}

