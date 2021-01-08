<?php
/**
 * PHPUnitArrayAssertions - Array-related PHPUnit assertions for API testing.
 *
 * @copyright Copyright (c) 2020, Daniel Rudolf (<https://www.daniel-rudolf.de>)
 *
 * This file is copyrighted by the contributors recorded in the version control
 * history of the file, available from the following original location:
 *
 * <https://github.com/PhrozenByte/phpunit-array-asserts/blob/master/src/Constraint/SequentialArray.php>
 *
 * @license http://opensource.org/licenses/MIT The MIT License
 *
 * SPDX-License-Identifier: MIT
 * License-Filename: LICENSE
 */

declare(strict_types=1);

namespace PhrozenByte\PHPUnitArrayAsserts\Constraint;

use EmptyIterator;
use Generator;
use Iterator;
use IteratorAggregate;
use NoRewindIterator;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\InvalidArgumentException;
use Traversable;

/**
 * Constraint that asserts that a value is like a sequential array, has a
 * minimum and/or maximum number of items, and that all items pass another
 * constraint.
 *
 * Sequential arrays are defined as ordered lists with incrementing numeric
 * keys starting from zero. This is especially true for native sequential
 * arrays like `[ "foo", "bar" ]`. Empty arrays are considered valid, too.
 * Traversable objects must have sequential keys to be considered valid.
 *
 * This constraint will fully traverse any Traversable object given. This also
 * means that any Generator will be fully exhausted. If possible, it will try
 * to restore an Iterator's pointer to its previous state.
 *
 * The expected minimum and/or maximum number of items, and the constraint
 * to apply all items to, are passed in the constructor.
 */
class SequentialArray extends Constraint
{
    /** @var int */
    protected $minItems;

    /** @var int|null */
    protected $maxItems;

    /** @var Constraint */
    protected $constraint;

    /**
     * SequentialArray constructor.
     *
     * @param int             $minItems   required minimum number of items, defaults to 0
     * @param int|null        $maxItems   required maximum number of items, defaults to NULL (infinite)
     * @param Constraint|null $constraint optional constraint to apply all items to (defaults to NULL)
     *
     * @throws InvalidArgumentException
     */
    public function __construct(int $minItems = 0, int $maxItems = null, Constraint $constraint = null)
    {
        if ($minItems < 0) {
            throw InvalidArgumentException::create(1, 'non-negative integer');
        }

        if ($maxItems !== null) {
            if ($maxItems < 0) {
                throw InvalidArgumentException::create(2, 'non-negative integer');
            } elseif ($minItems > $maxItems) {
                throw InvalidArgumentException::create(2, 'integer not lesser than argument #2');
            }
        }

        $this->minItems = $minItems;
        $this->maxItems = $maxItems;
        $this->constraint = $constraint;
    }

    /**
     * {@inheritDoc}
     */
    public function toString(): string
    {
        if ($this->maxItems === 0) {
            return 'is an empty array';
        }

        if (($this->minItems <= 1) && ($this->maxItems === null)) {
            $text = 'is a' . (($this->minItems > 0) ? ' non-empty' : '') . ' sequential array';
            $text .= ($this->constraint !== null) ? ' whose items match' : '';
        } else {
            $text = 'is a sequential array';
            if ($this->minItems && $this->maxItems) {
                if ($this->minItems === $this->maxItems) {
                    $text .= ' with exactly ' . $this->minItems . ' ' . (($this->minItems > 1) ? 'items' : 'item');
                } else {
                    $text .= ' with ≥ ' . $this->minItems . ' and ≤ ' . $this->maxItems . ' items';
                }
            } elseif ($this->minItems) {
                $text .= ' with ≥ ' . $this->minItems . ' items';
            } else {
                $text .= ' with ≤ ' . $this->maxItems . ' items';
            }

            $text .= ($this->constraint !== null) ? ' matching' : '';
        }

        if ($this->constraint !== null) {
            $text .= sprintf(' the constraint "%s"', $this->constraint->toString());
        }

        return $text;
    }

    /**
     * {@inheritDoc}
     */
    protected function matches($other): bool
    {
        [ $valid, $itemCount, $itemsValid ] = $this->inspectData($other);

        if (!$valid) {
            return false;
        }

        if (!$itemsValid) {
            return false;
        }

        if ($itemCount < $this->minItems) {
            return false;
        }

        if (($this->maxItems !== null) && ($itemCount > $this->maxItems)) {
            return false;
        }

        return true;
    }

    /**
     * Inspects the given data and returns meta data.
     *
     * The returned array consists of three items: A boolean indicating whether
     * the data structure is like a sequential array, an integer representing
     * the number of items, and a boolean indicating whether all items match
     * the given constraint.
     *
     * Sequential arrays are defined as ordered lists with incrementing numeric
     * keys starting from zero. This is especially true for native sequential
     * arrays like `[ "foo", "bar" ]`. Empty arrays are considered valid, too.
     * Traversable objects must have sequential keys to be considered valid.
     * The first item of the result array will hold TRUE for sequential data,
     * FALSE otherwise.
     *
     * Please note that this method will fully traverse a Traversable object.
     * If an Iterator is given, this method will try to restore the object's
     * pointer to its previous state. This will silently fail for instances of
     * NoRewindIterator. Generators will be fully exhausted by this method. The
     * behaviour for Iterators with non-unique keys is undefined.
     *
     * The second item of the result array holds an integer representing the
     * number of items given. The integer will be ≥ 0 for any traversable data,
     * or -1 for non-traversable data.
     *
     * Optionally this method will also apply all values to a given constraint.
     * If all items pass the constraint, the third item of the result array
     * will be TRUE, FALSE otherwise. If non-traversable data is passed, it
     * will return FALSE, too.
     *
     * Example:
     * ```php
     * [ $valid, $itemCount, $itemsValid ] = $this->inspectData([ "foo", "bar" ]);
     * // $valid = true; $itemCount = 2; $itemsValid = true;
     * ```
     *
     * @param mixed $other value to inspect
     *
     * @return array{0: bool, 1: int, 2: bool} inspection result
     */
    protected function inspectData($other): array
    {
        if (is_array($other)) {
            $itemCount = count($other);
            $valid = (($itemCount === 0) || (isset($other[0]) && ($other === array_values($other))));

            $itemsValid = true;
            if ($valid && ($this->constraint !== null)) {
                foreach ($other as $item) {
                    if (!$this->constraint->evaluate($item, '', true)) {
                        $itemsValid = false;
                        break;
                    }
                }
            }

            return [ $valid, $itemCount, $itemsValid ];
        }

        if ($other instanceof EmptyIterator) {
            return [ true, 0, true ];
        }

        if ($other instanceof Traversable) {
            while ($other instanceof IteratorAggregate) {
                $other = $other->getIterator();
            }

            $restorePointer = null;
            if ($other instanceof Iterator) {
                if (!($other instanceof Generator) && !($other instanceof NoRewindIterator)) {
                    $restorePointer = $other->valid() ? $other->key() : null;
                }
            }

            $valid = true;
            $itemCount = 0;
            $itemsValid = true;
            foreach ($other as $key => $item) {
                if ($key !== $itemCount++) {
                    $valid = false;
                }

                if ($valid && ($this->constraint !== null) && !$this->constraint->evaluate($item, '', true)) {
                    $itemsValid = false;
                }
            }

            if ($restorePointer !== null) {
                $other->rewind();
                while ($other->valid() && ($other->key() !== $restorePointer)) {
                    $other->next();
                }
            }

            return [ $valid, $itemCount, $itemsValid ];
        }

        return [ false, -1, false ];
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return ($this->constraint !== null) ? $this->constraint->count() + 1 : 1;
    }
}
