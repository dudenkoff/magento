<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Demo command to test model save with automatic reindexing via plugin
 */

namespace Dudenkoff\IndexerLearn\Console\Command;

use Dudenkoff\IndexerLearn\Model\ProductStatsFactory;
use Dudenkoff\IndexerLearn\Model\ResourceModel\ProductStats as ProductStatsResource;
use Dudenkoff\IndexerLearn\Model\Indexer\ProductStatsProcessor;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestModelSaveCommand extends Command
{
    const OPTION_PRODUCT_ID = 'product-id';
    const OPTION_VIEWS = 'views';

    /**
     * @var ProductStatsFactory
     */
    private $productStatsFactory;

    /**
     * @var ProductStatsResource
     */
    private $productStatsResource;

    /**
     * @var ProductStatsProcessor
     */
    private $productStatsProcessor;

    /**
     * @param ProductStatsFactory $productStatsFactory
     * @param ProductStatsResource $productStatsResource
     * @param ProductStatsProcessor $productStatsProcessor
     * @param string|null $name
     */
    public function __construct(
        ProductStatsFactory $productStatsFactory,
        ProductStatsResource $productStatsResource,
        ProductStatsProcessor $productStatsProcessor,
        ?string $name = null
    ) {
        parent::__construct($name);
        $this->productStatsFactory = $productStatsFactory;
        $this->productStatsResource = $productStatsResource;
        $this->productStatsProcessor = $productStatsProcessor;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('dudenkoff:indexer:test-model-save');
        $this->setDescription('Demo: Test automatic reindexing after model save using plugin');
        $this->addOption(
            self::OPTION_PRODUCT_ID,
            'p',
            InputOption::VALUE_REQUIRED,
            'Product ID to update (default: 1)',
            1
        );
        $this->addOption(
            self::OPTION_VIEWS,
            null,
            InputOption::VALUE_REQUIRED,
            'View count to add (default: 10)',
            10
        );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $productId = (int)$input->getOption(self::OPTION_PRODUCT_ID);
        $views = (int)$input->getOption(self::OPTION_VIEWS);

        $output->writeln('');
        $output->writeln('<info>=== MODEL SAVE WITH PLUGIN DEMO ===</info>');
        $output->writeln('');

        // Show current indexer mode
        $isScheduled = $this->productStatsProcessor->isIndexerScheduled();
        $mode = $isScheduled ? 'Schedule (Update on Schedule)' : 'Realtime (Update on Save)';
        $output->writeln("<comment>Current Indexer Mode:</comment> {$mode}");
        $output->writeln('');

        try {
            // Load the model
            $output->writeln("<info>Step 1: Loading ProductStats model for product {$productId}...</info>");
            $model = $this->productStatsFactory->create();
            $this->productStatsResource->load($model, $productId, 'product_id');

            if (!$model->getId()) {
                $output->writeln("<error>Product {$productId} not found!</error>");
                return Cli::RETURN_FAILURE;
            }

            $oldViews = $model->getData('view_count');
            $output->writeln("  Current view count: {$oldViews}");
            $output->writeln('');

            // Update the model
            $output->writeln("<info>Step 2: Updating view count (+{$views})...</info>");
            $newViews = $oldViews + $views;
            $model->setData('view_count', $newViews);

            // Save the model - Plugin will automatically trigger reindex!
            $output->writeln("<info>Step 3: Saving model...</info>");
            $output->writeln("  <comment>Plugin 'ReindexAfterSavePlugin' will intercept the save operation</comment>");
            $output->writeln('');
            
            $this->productStatsResource->save($model);
            
            $output->writeln('  <info>✓ Model saved successfully!</info>');
            $output->writeln("  New view count: {$newViews}");
            $output->writeln('');

            // Explain what happened
            $output->writeln('<info>Step 4: What happened behind the scenes:</info>');
            $output->writeln('  1. Model->save() was called');
            $output->writeln('  2. ResourceModel saved data to database');
            $output->writeln('  3. <comment>Plugin intercepted afterSave()</comment>');
            $output->writeln('  4. Plugin called ProductStatsProcessor->reindexRow()');
            
            if (!$isScheduled) {
                $output->writeln('  5. <comment>⚡ Processor triggered immediate reindex (Realtime mode)</comment>');
                $output->writeln('  6. Index is now up-to-date!');
            } else {
                $output->writeln('  5. <comment>⏰ Processor skipped reindex (Schedule mode)</comment>');
                $output->writeln('  6. Change logged to changelog table');
                $output->writeln('  7. Cron will process it later');
            }
            $output->writeln('');

            // Show plugin details
            $output->writeln('<comment>Plugin Configuration (di.xml):</comment>');
            $output->writeln('  <type name="Dudenkoff\IndexerLearn\Model\ResourceModel\ProductStats">');
            $output->writeln('      <plugin name="dudenkoff_reindex_after_save"');
            $output->writeln('              type="Dudenkoff\IndexerLearn\Plugin\ReindexAfterSavePlugin"');
            $output->writeln('              sortOrder="100"/>');
            $output->writeln('  </type>');
            $output->writeln('');

            // Show verification
            $output->writeln('<comment>Verify the index was updated:</comment>');
            $output->writeln('  bin/magento dudenkoff:indexer:show-stats --limit 5');
            $output->writeln('');

            // Show how to test both modes
            $output->writeln('<comment>Test Different Modes:</comment>');
            $output->writeln('  # Switch to realtime and test');
            $output->writeln('  bin/magento indexer:set-mode realtime dudenkoff_product_stats');
            $output->writeln('  bin/magento dudenkoff:indexer:test-model-save -p 1 --views=10');
            $output->writeln('');
            $output->writeln('  # Switch to schedule and test');
            $output->writeln('  bin/magento indexer:set-mode schedule dudenkoff_product_stats');
            $output->writeln('  bin/magento dudenkoff:indexer:test-model-save -p 1 --views=10');
            $output->writeln('');

            return Cli::RETURN_SUCCESS;

        } catch (\Exception $e) {
            $output->writeln("<error>Error: {$e->getMessage()}</error>");
            $output->writeln("<error>Trace: {$e->getTraceAsString()}</error>");
            return Cli::RETURN_FAILURE;
        }
    }
}

