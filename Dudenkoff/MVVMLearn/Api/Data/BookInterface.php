<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Book Data Interface (Service Contract)
 */

namespace Dudenkoff\MVVMLearn\Api\Data;

interface BookInterface
{
    const BOOK_ID = 'book_id';
    const TITLE = 'title';
    const AUTHOR = 'author';
    const ISBN = 'isbn';
    const DESCRIPTION = 'description';
    const PRICE = 'price';
    const STOCK_QTY = 'stock_qty';
    const STATUS = 'status';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * @return int|null
     */
    public function getBookId();

    /**
     * @param int $bookId
     * @return $this
     */
    public function setBookId($bookId);

    /**
     * @return string|null
     */
    public function getTitle();

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * @return string|null
     */
    public function getAuthor();

    /**
     * @param string $author
     * @return $this
     */
    public function setAuthor($author);

    /**
     * @return string|null
     */
    public function getIsbn();

    /**
     * @param string $isbn
     * @return $this
     */
    public function setIsbn($isbn);

    /**
     * @return string|null
     */
    public function getDescription();

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * @return float|null
     */
    public function getPrice();

    /**
     * @param float $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * @return int|null
     */
    public function getStockQty();

    /**
     * @param int $stockQty
     * @return $this
     */
    public function setStockQty($stockQty);

    /**
     * @return int|null
     */
    public function getStatus();

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * @return string|null
     */
    public function getUpdatedAt();
}

