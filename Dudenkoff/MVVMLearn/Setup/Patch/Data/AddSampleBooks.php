<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Data patch to add sample books
 */

namespace Dudenkoff\MVVMLearn\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class AddSampleBooks implements DataPatchInterface
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

        $table = $this->moduleDataSetup->getTable('dudenkoff_book');
        
        $data = [
            [
                'title' => 'PHP for Beginners',
                'author' => 'John Doe',
                'isbn' => '978-1234567890',
                'description' => 'A comprehensive guide to PHP programming',
                'price' => 29.99,
                'stock_qty' => 50,
                'status' => 1
            ],
            [
                'title' => 'Magento 2 Developer Guide',
                'author' => 'Jane Smith',
                'isbn' => '978-0987654321',
                'description' => 'Complete guide to Magento 2 development',
                'price' => 49.99,
                'stock_qty' => 30,
                'status' => 1
            ],
            [
                'title' => 'MySQL Performance Tuning',
                'author' => 'Bob Johnson',
                'isbn' => '978-1111111111',
                'description' => 'Optimize your MySQL queries',
                'price' => 39.99,
                'stock_qty' => 0,
                'status' => 1
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

