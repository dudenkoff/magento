<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Demo command showing realtime indexer usage
 */

namespace Dudenkoff\IndexerLearn\Console\Command;

use Dudenkoff\IndexerLearn\Service\ProductStatsService;
use Dudenkoff\IndexerLearn\Model\Indexer\ProductStatsProcessor;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class DemoRealtimeIndexerCommand extends Command
{
    const OPTION_PRODUCT_ID = 'product-id';
    const OPTION_VIEWS = 'views';
    const OPTION_REVENUE = 'revenue';

    /**
     * @var ProductStatsService
     */
    private $productStatsService;

    /**
     * @var ProductStatsProcessor
     */
    private $productStatsProcessor;

    /**
     * @param ProductStatsService $productStatsService
     * @param ProductStatsProcessor $productStatsProcessor
     * @param string|null $name
     */
    public function __construct(
        ProductStatsService $productStatsService,
        ProductStatsProcessor $productStatsProcessor,
        ?string $name = null
    ) {
        parent::__construct($name);
        $this->productStatsService = $productStatsService;
        $this->productStatsProcessor = $productStatsProcessor;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('dudenkoff:indexer:demo-realtime');
        $this->setDescription('Demo: How to use custom realtime indexer with ProductStatsProcessor');
        $this->addOption(
            self::OPTION_PRODUCT_ID,
            'p',
            InputOption::VALUE_REQUIRED,
            'Product ID (default: 1)',
            1
        );
        $this->addOption(
            self::OPTION_VIEWS,
            null,
            InputOption::VALUE_OPTIONAL,
            'Views to add'
        );
        $this->addOption(
            self::OPTION_REVENUE,
            'r',
            InputOption::VALUE_OPTIONAL,
            'Revenue to add'
        );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $productId = (int)$input->getOption(self::OPTION_PRODUCT_ID);
        $views = $input->getOption(self::OPTION_VIEWS);
        $revenue = $input->getOption(self::OPTION_REVENUE);

        $output->writeln('');
        $output->writeln('<info>=== REALTIME INDEXER DEMO ===</info>');
        $output->writeln('');

        // Show current indexer mode
        $isScheduled = $this->productStatsProcessor->isIndexerScheduled();
        $mode = $isScheduled ? 'Schedule (Update on Schedule)' : 'Realtime (Update on Save)';
        $output->writeln("<comment>Current Indexer Mode:</comment> {$mode}");
        $output->writeln('');

        // Example 1: Increment view count
        if ($views !== null) {
            $output->writeln("<info>Example 1: Increment View Count</info>");
            $output->writeln("  Product ID: {$productId}");
            $output->writeln("  Views to add: {$views}");
            $output->writeln('');

            $success = $this->productStatsService->incrementViewCount($productId, (int)$views);

            if ($success) {
                $output->writeln('  <info>✓ Views updated successfully!</info>');
                if (!$isScheduled) {
                    $output->writeln('  <comment>⚡ Indexer triggered immediately (Realtime mode)</comment>');
                } else {
                    $output->writeln('  <comment>⏰ Change logged to changelog (Schedule mode)</comment>');
                }
            } else {
                $output->writeln('  <error>✗ Failed to update views</error>');
            }
            $output->writeln('');
        }

        // Example 2: Record purchase
        if ($revenue !== null) {
            $output->writeln("<info>Example 2: Record Purchase</info>");
            $output->writeln("  Product ID: {$productId}");
            $output->writeln("  Revenue: \${$revenue}");
            $output->writeln('');

            $success = $this->productStatsService->recordPurchase($productId, (float)$revenue);

            if ($success) {
                $output->writeln('  <info>✓ Purchase recorded successfully!</info>');
                if (!$isScheduled) {
                    $output->writeln('  <comment>⚡ Indexer triggered immediately (Realtime mode)</comment>');
                } else {
                    $output->writeln('  <comment>⏰ Change logged to changelog (Schedule mode)</comment>');
                }
            } else {
                $output->writeln('  <error>✗ Failed to record purchase</error>');
            }
            $output->writeln('');
        }

        // Example 3: Batch update
        if ($views === null && $revenue === null) {
            $output->writeln("<info>Example 3: Batch Update (Demo)</info>");
            $output->writeln('');

            $updates = [
                ['product_id' => 1, 'views' => 10],
                ['product_id' => 2, 'views' => 20],
                ['product_id' => 3, 'views' => 30],
            ];

            $output->writeln('  Batch updating 3 products...');
            $count = $this->productStatsService->batchUpdateViews($updates);

            $output->writeln("  <info>✓ Updated {$count} products</info>");
            if (!$isScheduled) {
                $output->writeln('  <comment>⚡ Batch indexer triggered (Realtime mode)</comment>');
            } else {
                $output->writeln('  <comment>⏰ Changes logged to changelog (Schedule mode)</comment>');
            }
            $output->writeln('');
        }

        // Show usage instructions
        $output->writeln('<comment>Usage Examples:</comment>');
        $output->writeln('  bin/magento dudenkoff:indexer:demo-realtime --product-id=1 --views=100');
        $output->writeln('  bin/magento dudenkoff:indexer:demo-realtime -p 2 -r 250.50');
        $output->writeln('  bin/magento dudenkoff:indexer:demo-realtime  # Run batch demo');
        $output->writeln('');

        $output->writeln('<comment>Test Different Modes:</comment>');
        $output->writeln('  # Switch to realtime');
        $output->writeln('  bin/magento indexer:set-mode realtime dudenkoff_product_stats');
        $output->writeln('  bin/magento dudenkoff:indexer:demo-realtime -p 1 --views=10');
        $output->writeln('');
        $output->writeln('  # Switch to schedule');
        $output->writeln('  bin/magento indexer:set-mode schedule dudenkoff_product_stats');
        $output->writeln('  bin/magento dudenkoff:indexer:demo-realtime -p 1 --views=10');
        $output->writeln('');

        return Cli::RETURN_SUCCESS;
    }
}

