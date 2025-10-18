<?php
/**
 * Module Registration
 * 
 * This file registers the ObserverLearn module with Magento 2.
 */

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Dudenkoff_ObserverLearn',
    __DIR__
);

