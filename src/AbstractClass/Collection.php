<?php

namespace WebXID\EDMo\AbstractClass;

use InvalidArgumentException;
use Iterator;
use ArrayAccess;
use Countable;

/**
 * Class Collection
 * The class implements Iterator, ArrayAccess, Countable interfaces
 *
 * @see BasicEntity
 *
 * @package WebXID\EDMo\AbstractClass
 */
abstract class Collection extends BasicDataContainer implements Iterator, ArrayAccess, Countable
{
    /**
     * @var []
     */
    protected $collected_items = [];

    #region Abstract interface

    /**
     * @param object $object
     *
     * @return bool
     */
    abstract protected static function isEntityValid($object) : bool;

    #endregion

    #region Iterator Interface

    /**
     * Return the current object
     *
     * @return object|false
     */
    public function current()
    {
        return current($this->collected_items);
    }

    /**
     * Advance the internal array pointer of an array
     */
    public function next()
    {
        next($this->collected_items);
    }

    /**
     * Return the key of the current element
     *
     * @return int
     */
    public function key()
    {
        return key($this->collected_items);
    }

    /**
     * Checks if current position is valid
     *
     * @return bool
     */
    public function valid()
    {
        return current($this->collected_items) !== false;
    }

    /**
     * Rewind the Iterator to the first element
     */
    public function rewind()
    {
        reset($this->collected_items);
    }

    #endregion

    #region Iterator Methods

    /**
     * Whether a offset exists
     *
     * @param int $index
     *
     * @return bool
     * @internal param $int
     */
    public function offsetExists($index)
    {
        return isset($this->collected_items[$index]);
    }

    /**
     * @param int $index
     *
     * @return object|null
     */
    public function offsetGet($index)
    {
        if (!is_scalar($index)) {
            $type = is_object($index) ? get_class($index) : gettype($index);

            throw new InvalidArgumentException("Offset must be a integer type, {$type} given");
        }

        return isset($this->collected_items[$index]) ? $this->collected_items[$index] : null;
    }

    /**
     * @param $offset
     * @param object $object
     */
    public function offsetSet($offset, $object)
    {
        if (!$object instanceof CollectionItem) {
            $type = is_object($object) ? get_class($object) : gettype($object);

            throw new InvalidArgumentException("Class {$type} does not imploment " . CollectionItem::class);
        }

        if (!static::isEntityValid($object)) {
            $type = is_object($object) ? get_class($object) : gettype($object);

            throw new InvalidArgumentException("Invalid value instance, given {$type} . Please, check " . static::class . "::isEntityValid()");
        }

        if ($offset) {
            $this->collected_items[$offset] = $object;
        } else {
            $this->collected_items[] = $object;
        }
    }

    /**
     * Offset to unset
     *
     * @param int $index
     */
    public function offsetUnset($index) {
        unset($this->collected_items[$index]);
    }

    #endregion

    #region Countable Interface

    /**
     * Count elements of an object
     *
     * @return int
     */
    public function count()
    {
        return count($this->collected_items);
    }

    #endregion

    #region Getters

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $result = parent::toArray();

        foreach ($this->collected_items as $key => $entity) {
            $result['collected_items'][$key] = $entity->toArray();
        }

        return $result;
    }

    #endregion
}
