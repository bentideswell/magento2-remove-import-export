<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Import;

use Magento\ImportExport\Model\Import\AbstractEntity;

/**
 * Data source with columns for Magento_ImportExport
 *
 * @api
 * @since 100.0.2
 */
abstract class AbstractSource implements \SeekableIterator
{
    /**
     * Get and validate column names
     *
     * @param array $colNames
     * @throws \InvalidArgumentException
     */
    public function __construct(array $colNames)
    {
    }

    /**
     * Return the current element
     *
     * Returns the row in associative array format: array(<col_name> => <value>, ...)
     *
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
    }

    /**
     * Move forward to next element (\Iterator interface)
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
    }

    /**
     * Return the key of the current element (\Iterator interface)
     *
     * @return int -1 if out of bounds, 0 or more otherwise
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
    }

    /**
     * Checks if current position is valid (\Iterator interface)
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
    }

    /**
     * Rewind the \Iterator to the first element (\Iterator interface)
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
    }

    /**
     * Seeks to a position (Seekable interface)
     *
     * @param int $position The position to seek to 0 or more
     * @return void
     * @throws \OutOfBoundsException
     */
    #[\ReturnTypeWillChange]
    public function seek($position)
    {
    }}
