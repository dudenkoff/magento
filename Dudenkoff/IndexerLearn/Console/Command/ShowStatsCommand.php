<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * CLI Command to display indexed statistics
 */

namespace Dudenkoff\IndexerLearn\Console\Command;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ShowStatsCommand extends Command
{
    private const OPTION_LIMIT = 'limit';
    private const OPTION_TIER = 'tier';

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
        $this->setName('dudenkoff:indexer:show-stats');
        $this->setDescription('Display indexed product statistics');
        $this->addOption(
            self::OPTION_LIMIT,
            'l',
            InputOption::VALUE_OPTIONAL,
            'Number of products to show (default: 20)',
            20
        );
        $this->addOption(
            self::OPTION_TIER,
            't',
            InputOption::VALUE_OPTIONAL,
            'Filter by popularity tier (low/medium/high)',
            null
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
        $limit = (int)$input->getOption(self::OPTION_LIMIT);
        $tier = $input->getOption(self::OPTION_TIER);

        $connection = $this->resourceConnection->getConnection();
        $sourceTable = $this->resourceConnection->getTableName('dudenkoff_product_stats');
        $indexTable = $this->resourceConnection->getTableName('dudenkoff_product_stats_idx');

        try {
            // Check if index table has data
            $indexCount = $connection->fetchOne("SELECT COUNT(*) FROM {$indexTable}");
            $sourceCount = $connection->fetchOne("SELECT COUNT(*) FROM {$sourceTable}");

            $output->writeln('');
            $output->writeln('<info>=== Indexer Statistics ===</info>');
            $output->writeln('Source table records:  ' . $sourceCount);
            $output->writeln('Indexed table records: ' . $indexCount);
            
            if ($indexCount == 0) {
                $output->writeln('');
                $output->writeln('<comment>⚠ Index table is empty. Run: bin/magento indexer:reindex dudenkoff_product_stats</comment>');
                return Cli::RETURN_SUCCESS;
            }

            $output->writeln('');

            // Fetch indexed data
            $select = $connection->select()
                ->from($indexTable)
                ->order('conversion_rate DESC')
                ->limit($limit);

            if ($tier) {
                $select->where('popularity_tier = ?', $tier);
            }

            $data = $connection->fetchAll($select);

            if (empty($data)) {
                $output->writeln('<comment>No data found</comment>');
                return Cli::RETURN_SUCCESS;
            }

            // Display as table
            $table = new Table($output);
            $table->setHeaders([
                'Product ID',
                'Views',
                'Purchases',
                'Revenue',
                'Conv. Rate %',
                'AOV',
                'Tier',
                'Indexed At'
            ]);

            foreach ($data as $row) {
                $table->addRow([
                    $row['product_id'],
                    $row['view_count'],
                    $row['purchase_count'],
                    '$' . number_format($row['revenue'], 2),
                    $row['conversion_rate'] . '%',
                    '$' . number_format($row['average_order_value'], 2),
                    $row['popularity_tier'],
                    $row['indexed_at']
                ]);
            }

            $table->render();

            // Show summary by tier
            $output->writeln('');
            $output->writeln('<info>=== Summary by Popularity Tier ===</info>');
            
            $tierStats = $connection->fetchAll(
                "SELECT 
                    popularity_tier,
                    COUNT(*) as count,
                    AVG(conversion_rate) as avg_conversion,
                    SUM(revenue) as total_revenue
                FROM {$indexTable}
                GROUP BY popularity_tier
                ORDER BY 
                    CASE popularity_tier
                        WHEN 'high' THEN 1
                        WHEN 'medium' THEN 2
                        WHEN 'low' THEN 3
                    END"
            );

            $tierTable = new Table($output);
            $tierTable->setHeaders(['Tier', 'Products', 'Avg Conversion %', 'Total Revenue']);
            foreach ($tierStats as $stat) {
                $tierTable->addRow([
                    strtoupper($stat['popularity_tier']),
                    $stat['count'],
                    number_format($stat['avg_conversion'], 2) . '%',
                    '$' . number_format($stat['total_revenue'], 2)
                ]);
            }
            $tierTable->render();

            $output->writeln('');
            $output->writeln('<comment>💡 This data is pre-calculated in the index table for fast queries!</comment>');

            return Cli::RETURN_SUCCESS;

        } catch (\Exception $e) {
            $output->writeln('<error>Error: ' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }
    }
}


