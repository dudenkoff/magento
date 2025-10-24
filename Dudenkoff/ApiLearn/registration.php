<?php
/**
 * Module Registration
 * 
 * Registers the ApiLearn module for learning Web API concepts.
 */

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Dudenkoff_ApiLearn',
    __DIR__
);


