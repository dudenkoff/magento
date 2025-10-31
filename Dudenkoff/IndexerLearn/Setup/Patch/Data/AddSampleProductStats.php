<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Data patch to add sample product statistics for testing
 */

namespace Dudenkoff\IndexerLearn\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class AddSampleProductStats implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $table = $this->moduleDataSetup->getTable('dudenkoff_product_stats');
        
        $data = [
            [
                'product_id' => 1,
                'view_count' => 1500,
                'purchase_count' => 300,
                'revenue' => 15000.00
            ],
            [
                'product_id' => 2,
                'view_count' => 500,
                'purchase_count' => 50,
                'revenue' => 2500.00
            ],
            [
                'product_id' => 3,
                'view_count' => 50,
                'purchase_count' => 5,
                'revenue' => 250.00
            ]
        ];

        $this->moduleDataSetup->getConnection()->insertMultiple($table, $data);

        $this->moduleDataSetup->getConnection()->endSetup();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}

