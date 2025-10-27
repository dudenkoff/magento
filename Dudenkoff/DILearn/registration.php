<?php
/**
 * Module Registration
 * 
 * This file registers the module with Magento 2.
 * Magento scans all registration.php files during bootstrap.
 */

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Dudenkoff_DILearn',
    __DIR__
);

