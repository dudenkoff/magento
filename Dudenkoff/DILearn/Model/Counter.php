<?php
/**
 * Counter Model
 * 
 * DEMONSTRATES: Shared vs Non-Shared instances
 * 
 * In di.xml, this is set as shared="false"
 * This means each time you inject Counter, you get a NEW instance.
 * 
 * DEFAULT BEHAVIOR: shared="true" (singleton pattern)
 * - Same instance is reused everywhere
 * - State is shared across all uses
 * 
 * WITH shared="false":
 * - New instance created each time
 * - Each instance has its own state
 */

namespace Dudenkoff\DILearn\Model;

class Counter
{
    /**
     * @var int
     */
    private $count = 0;

    /**
     * Increment the counter
     *
     * @return int New count value
     */
    public function increment(): int
    {
        return ++$this->count;
    }

    /**
     * Get current count
     *
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }
}

