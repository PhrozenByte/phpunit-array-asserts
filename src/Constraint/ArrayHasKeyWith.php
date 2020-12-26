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

/**
 * Constraint that asserts that an array has a given key and that its value
 * passes another constraint.
 *
 * Accepts both native arrays and ArrayAccess objects. The constraint will fail
 * if the key doesn't exist in the array.
 *
 * The item's key and the constraint the value must pass are passed in the
 * constructor.
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
     * @param int|string $key        the key of the item to check
     * @param Constraint $constraint the constraint the item's value is applied to
     */
    public function __construct($key, Constraint $constraint)
    {
        $this->key = $key;
        $this->constraint = $constraint;
    }

    /**
     * {@inheritDoc}
     */
    public function toString(): string
    {
        return 'has the key ' . $this->exporter()->export($this->key) . ' '
            . 'whose value ' . $this->constraint->toString();
    }

    /**
     * {@inheritDoc}
     */
    public function evaluate($other, string $description = '', bool $returnResult = false)
    {
        $success = false;
        if (is_array($other)) {
            $success = array_key_exists($this->key, $other);
        } elseif ($other instanceof ArrayAccess) {
            $success = $other->offsetExists($this->key);
        }

        if (!$success) {
            if ($returnResult) {
                return false;
            }

            $this->fail($other, $description);
        }

        return $this->constraint->evaluate($other[$this->key], $description, $returnResult);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return $this->constraint->count() + 1;
    }

    /**
     * {@inheritDoc}
     */
    protected function failureDescription($other): string
    {
        return 'an array ' . $this->toString();
    }
}
