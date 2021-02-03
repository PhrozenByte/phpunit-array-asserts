<?php
/**
 * PHPUnitArrayAssertions - Array-related PHPUnit assertions for API testing.
 *
 * @copyright Copyright (c) 2020, Daniel Rudolf (<https://www.daniel-rudolf.de>)
 *
 * This file is copyrighted by the contributors recorded in the version control
 * history of the file, available from the following original location:
 *
 * <https://github.com/PhrozenByte/phpunit-array-asserts/blob/master/src/Constraint/ArrayHasItemWith.php>
 *
 * @license http://opensource.org/licenses/MIT The MIT License
 *
 * SPDX-License-Identifier: MIT
 * License-Filename: LICENSE
 */

declare(strict_types=1);

namespace PhrozenByte\PHPUnitArrayAsserts\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsEqual;
use Traversable;

/**
 * Constraint that asserts that an array has a item at a given index and that
 * its value passes another constraint.
 *
 * Accepts both native arrays and Traversable objects. The constraint will fail
 * if the array has less items than required.
 *
 * This constraint will fully traverse any Traversable object given. This also
 * means that any Generator will be fully exhausted. It doesn't restore an
 * Iterator's pointer to its previous state.
 *
 * The index of the item to check, and the constraint its value must pass are
 * passed in the constructor. The constraint can either be an arbitrary
 * `Constraint` instance (e.g. `PHPUnit\Framework\Constraint\StringContains`),
 * or any static value, requiring an exact match of the value.
 */
class ArrayHasItemWith extends Constraint
{
    /** @var int */
    protected $index;

    /** @var Constraint */
    protected $constraint;

    /**
     * ArrayHasItemWith constructor.
     *
     * @param int              $index      the index of the item to check
     * @param Constraint|mixed $constraint the constraint the item's value is applied to
     */
    public function __construct(int $index, $constraint)
    {
        $this->index = $index;
        $this->constraint = !($constraint instanceof Constraint) ? new IsEqual($constraint) : $constraint;
    }

    /**
     * Returns a human-readable string representation of this Constraint.
     *
     * @return string string representation of the Constraint
     */
    public function toString(): string
    {
        return 'is an array that has a value at index ' . $this->index . ' '
                . 'which ' . $this->constraint->toString();
    }

    /**
     * Returns whether the given value matches the Constraint.
     *
     * @param mixed $other the value to evaluate
     *
     * @return bool boolean indicating whether the value matches the Constraint
     */
    protected function matches($other): bool
    {
        $valueExists = false;
        if (is_array($other)) {
            $other = array_values($other);
            $valueExists = isset($other[$this->index]);
        } elseif ($other instanceof Traversable) {
            $other = iterator_to_array($other, false);
            $valueExists = isset($other[$this->index]);
        }

        if (!$valueExists) {
            return false;
        }

        return $this->constraint->evaluate($other[$this->index], '', true);
    }

    /**
     * Returns the number of assertions performed by this Constraint.
     */
    public function count(): int
    {
        return $this->constraint->count() + 1;
    }
}
