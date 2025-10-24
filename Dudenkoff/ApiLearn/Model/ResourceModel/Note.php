<?php
/**
 * Note Resource Model
 * 
 * Handles direct database operations for Note entity.
 * In real module, you'd create database table via db_schema.xml.
 * For learning, we use in-memory storage.
 */

namespace Dudenkoff\ApiLearn\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Note extends AbstractDb
{
    /**
     * Initialize resource model
     * 
     * In real module: connects to database table
     * For learning: simulated
     */
    protected function _construct()
    {
        // In real module: $this->_init('dudenkoff_note', 'note_id');
        // For learning purposes, we'll use in-memory storage via repository
        $this->_init('dudenkoff_note_table', 'note_id');
    }
}


