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
     * Connects this resource model to the database table.
     * 
     * PARAMETERS:
     * - Table name: dudenkoff_note_table (from db_schema.xml)
     * - Primary key: note_id
     */
    protected function _construct()
    {
        $this->_init('dudenkoff_note_table', 'note_id');
    }
}


