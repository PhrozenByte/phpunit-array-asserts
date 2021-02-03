<?php
/**
 * PHPUnitArrayAssertions - Array-related PHPUnit assertions for API testing.
 *
 * @copyright Copyright (c) 2020, Daniel Rudolf (<https://www.daniel-rudolf.de>)
 *
 * This file is copyrighted by the contributors recorded in the version control
 * history of the file, available from the following original location:
 *
 * <https://github.com/PhrozenByte/phpunit-array-asserts/blob/master/src/Constraint/ArrayHasKeyWith.php>
 *
 * @license http://opensource.org/licenses/MIT The MIT License
 *
 * SPDX-License-Identifier: MIT
 * License-Filename: LICENSE
 */

declare(strict_types=1);

namespace PhrozenByte\PHPUnitArrayAsserts\Constraint;

use ArrayAccess;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\InvalidArgumentException;

/**
 * Constraint that asserts that an array has a given key and that its value
 * passes another constraint.
 *
 * Accepts both native arrays and ArrayAccess objects. The constraint will fail
 * if the key doesn't exist in the array.
 *
 * The item's key and the constraint the value must pass are passed in the
 * constructor. The constraint can either be an arbitrary `Constraint` instance
 * (e.g. `PHPUnit\Framework\Constraint\StringContains`), or any static value,
 * requiring an exact match of the value.
 */
class ArrayHasKeyWith extends Constraint
{
    /** @var int|string */
    protected $key;

    /** @var Constraint */
    protected $constraint;

    /**
     * ArrayHasKeyWith constructor.
     *
     * @param int|string       $key        the key of the item to check
     * @param Constraint|mixed $constraint the constraint the item's value is applied to
     *
     * @throws InvalidArgumentException
     */
    public function __construct($key, $constraint)
    {
        if (!(is_int($key) || is_string($key))) {
            throw InvalidArgumentException::create(1, 'integer or string');
        }

        $this->key = $key;
        $this->constraint = !($constraint instanceof Constraint) ? new IsEqual($constraint) : $constraint;
    }

    /**
     * Returns a human-readable string representation of this Constraint.
     *
     * @return string string representation of the Constraint
     */
    public function toString(): string
    {
        return 'is an array that has the key ' . $this->exporter()->export($this->key) . ' '
            . 'whose value ' . $this->constraint->toString();
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
            $valueExists = array_key_exists($this->key, $other);
        } elseif ($other instanceof ArrayAccess) {
            $valueExists = $other->offsetExists($this->key);
        }

        if (!$valueExists) {
            return false;
        }

        return $this->constraint->evaluate($other[$this->key], '', true);
    }

    /**
     * Returns the number of assertions performed by this Constraint.
     */
    public function count(): int
    {
        return $this->constraint->count() + 1;
    }
}
