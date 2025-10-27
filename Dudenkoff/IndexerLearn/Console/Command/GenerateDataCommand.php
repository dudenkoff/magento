<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * CLI Command to generate sample data for testing the indexer
 */

namespace Dudenkoff\IndexerLearn\Console\Command;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateDataCommand extends Command
{
    private const ARG_COUNT = 'count';

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
        $this->setName('dudenkoff:indexer:generate-data');
        $this->setDescription('Generate sample product statistics data for indexer testing');
        $this->addArgument(
            self::ARG_COUNT,
            InputArgument::OPTIONAL,
            'Number of products to generate (default: 100)',
            100
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
        $count = (int)$input->getArgument(self::ARG_COUNT);
        
        $output->writeln('<info>Generating ' . $count . ' sample product statistics...</info>');

        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('dudenkoff_product_stats');

        try {
            $data = [];
            for ($i = 1; $i <= $count; $i++) {
                $viewCount = rand(0, 2000);
                $purchaseCount = rand(0, (int)($viewCount * 0.3)); // Max 30% conversion
                $avgPrice = rand(10, 500);
                $revenue = $purchaseCount * $avgPrice;

                $data[] = [
                    'product_id' => 1000 + $i,
                    'view_count' => $viewCount,
                    'purchase_count' => $purchaseCount,
                    'revenue' => $revenue,
                ];

                // Insert in batches of 100
                if (count($data) >= 100 || $i === $count) {
                    $connection->insertOnDuplicate($tableName, $data);
                    $output->writeln('Inserted batch... (' . $i . '/' . $count . ')');
                    $data = [];
                }
            }

            $output->writeln('<info>✓ Successfully generated ' . $count . ' product statistics records</info>');
            $output->writeln('');
            $output->writeln('<comment>Next steps:</comment>');
            $output->writeln('1. Check indexer status: <info>bin/magento indexer:status dudenkoff_product_stats</info>');
            $output->writeln('2. Run full reindex:    <info>bin/magento indexer:reindex dudenkoff_product_stats</info>');
            $output->writeln('3. View indexed data:   <info>bin/magento dudenkoff:indexer:show-stats</info>');

            return Cli::RETURN_SUCCESS;

        } catch (\Exception $e) {
            $output->writeln('<error>Error: ' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }
    }
}

