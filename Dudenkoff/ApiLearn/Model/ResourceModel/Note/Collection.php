<?php
/**
 * Note Collection
 * 
 * WHAT IS A COLLECTION?
 * A collection represents multiple model instances from database.
 * It's used for:
 * - Loading multiple records
 * - Filtering
 * - Sorting
 * - Pagination
 * - Joining tables
 * 
 * KEY METHODS:
 * - addFieldToFilter() - WHERE clauses
 * - setOrder() - ORDER BY
 * - setPageSize() / setCurPage() - LIMIT/OFFSET
 * - getSelect() - Get underlying SQL SELECT
 * - load() - Execute query
 * 
 * USED BY:
 * Repository's getList() method uses collections for search.
 */

namespace Dudenkoff\ApiLearn\Model\ResourceModel\Note;

use Dudenkoff\ApiLearn\Model\Note;
use Dudenkoff\ApiLearn\Model\ResourceModel\Note as NoteResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * ID field name
     *
     * @var string
     */
    protected $_idFieldName = 'note_id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'dudenkoff_note_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'note_collection';

    /**
     * Define resource model
     * 
     * Links this collection to the Note model and resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Note::class, NoteResource::class);
    }
}


