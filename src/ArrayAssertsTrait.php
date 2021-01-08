<?php
/**
 * PHPUnitArrayAssertions - Array-related PHPUnit assertions for API testing.
 *
 * @copyright Copyright (c) 2020, Daniel Rudolf (<https://www.daniel-rudolf.de>)
 *
 * This file is copyrighted by the contributors recorded in the version control
 * history of the file, available from the following original location:
 *
 * <https://github.com/PhrozenByte/phpunit-array-asserts/blob/master/src/ArrayAssertsTrait.php>
 *
 * @license http://opensource.org/licenses/MIT The MIT License
 *
 * SPDX-License-Identifier: MIT
 * License-Filename: LICENSE
 */

declare(strict_types=1);

namespace PhrozenByte\PHPUnitArrayAsserts;

use ArrayAccess;
use PHPUnit\Framework\Assert as PHPUnitAssert;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\InvalidArgumentException;
use PhrozenByte\PHPUnitArrayAsserts\Constraint\ArrayHasItemWith;
use PhrozenByte\PHPUnitArrayAsserts\Constraint\ArrayHasKeyWith;
use PhrozenByte\PHPUnitArrayAsserts\Constraint\AssociativeArray;
use PhrozenByte\PHPUnitArrayAsserts\Constraint\SequentialArray;
use Traversable;

trait ArrayAssertsTrait
{
    /**
     * Asserts that a value is an associative array matching a given structure
     * and that the array's items pass other constraints.
     *
     * @param Constraint[]      $constraints     an associative array with the expected keys and constraints to apply
     * @param array|ArrayAccess $array           the associative array to check
     * @param bool              $allowMissing    whether missing items should fail the constraint (defaults to FALSE)
     * @param bool              $allowAdditional whether additional items should fail the constraint (defaults to TRUE);
     *                                           this option works for native arrays only
     * @param string            $message         additional information about the test
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public static function assertAssociativeArray(
        array $constraints,
        $array,
        bool $allowMissing = false,
        bool $allowAdditional = true,
        string $message = ''
    ): void {
        if (!(is_array($array) || ($array instanceof ArrayAccess))) {
            throw InvalidArgumentException::create(2, 'array or ArrayAccess');
        }

        if (!is_array($array) && !$allowAdditional) {
            throw InvalidArgumentException::create(2, 'array when argument #4 is set true');
        }

        $constraint = new AssociativeArray($constraints, $allowMissing, $allowAdditional);
        PHPUnitAssert::assertThat($array, $constraint, $message);
    }

    /**
     * Returns a new instance of the AssociativeArray constraint.
     *
     * @param Constraint[] $constraints     an associative array with the expected keys and constraints to apply
     * @param bool         $allowMissing    whether missing items should fail the constraint (defaults to FALSE)
     * @param bool         $allowAdditional whether additional items should fail the constraint (defaults to TRUE);
     *                                      this option works for native arrays only
     *
     * @return AssociativeArray
     *
     * @throws InvalidArgumentException
     */
    public static function associativeArray(
        array $constraints,
        bool $allowMissing = false,
        bool $allowAdditional = true
    ): AssociativeArray {
        return new AssociativeArray($constraints, $allowMissing, $allowAdditional);
    }

    /**
     * Asserts that an array has a given key and that its value passes another
     * constraint.
     *
     * @param int|string        $key        the key of the item to check
     * @param Constraint        $constraint the constraint the item's value is applied to
     * @param array|ArrayAccess $array      the array to check
     * @param string            $message    additional information about the test
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public static function assertArrayHasKeyWith($key, Constraint $constraint, $array, string $message = ''): void
    {
        if (!(is_array($array) || ($array instanceof ArrayAccess))) {
            throw InvalidArgumentException::create(3, 'array or ArrayAccess');
        }

        $constraint = new ArrayHasKeyWith($key, $constraint);
        PHPUnitAssert::assertThat($array, $constraint, $message);
    }

    /**
     * Returns a new instance of the ArrayHasKeyWith constraint.
     *
     * @param int|string $key        the key of the item to check
     * @param Constraint $constraint the constraint the item's value is applied to
     *
     * @return ArrayHasKeyWith
     *
     * @throws InvalidArgumentException
     */
    public static function arrayHasKeyWith($key, Constraint $constraint): ArrayHasKeyWith
    {
        return new ArrayHasKeyWith($key, $constraint);
    }

    /**
     * Asserts that a value is like a sequential array, has a minimum and/or
     * maximum number of items, and that all items pass another constraint.
     *
     * @param array|Traversable $array      the sequential array to check
     * @param int               $minItems   required minimum number of items, defaults to 0
     * @param int|null          $maxItems   required maximum number of items, defaults to NULL (infinite)
     * @param Constraint|null   $constraint optional constraint to apply all items to (defaults to NULL)
     * @param string            $message    additional information about the test
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public static function assertSequentialArray(
        $array,
        int $minItems,
        int $maxItems = null,
        Constraint $constraint = null,
        string $message = ''
    ): void {
        if (!(is_array($array) || ($array instanceof Traversable))) {
            throw InvalidArgumentException::create(3, 'array or Traversable');
        }

        $constraint = new SequentialArray($minItems, $maxItems, $constraint);
        PHPUnitAssert::assertThat($array, $constraint, $message);
    }

    /**
     * Returns a new instance of the SequentialArray constraint.
     *
     * @param int             $minItems   required minimum number of items, defaults to 0
     * @param int|null        $maxItems   required maximum number of items, defaults to NULL (infinite)
     * @param Constraint|null $constraint optional constraint to apply all items to (defaults to NULL)
     *
     * @return SequentialArray
     *
     * @throws InvalidArgumentException
     */
    public static function sequentialArray(
        int $minItems,
        int $maxItems = null,
        Constraint $constraint = null
    ): SequentialArray {
        return new SequentialArray($minItems, $maxItems, $constraint);
    }

    /**
     * Asserts that an array has a item at a given index and that its value
     * passes another constraint.
     *
     * @param int               $index      the index of the item to check
     * @param Constraint        $constraint the constraint the item's value is applied to
     * @param array|Traversable $array      the array to check
     * @param string            $message    additional information about the test
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public static function assertArrayHasItemWith(
        int $index,
        Constraint $constraint,
        $array,
        string $message = ''
    ): void {
        if (!(is_array($array) || ($array instanceof Traversable))) {
            throw InvalidArgumentException::create(3, 'array or Traversable');
        }

        $constraint = new ArrayHasItemWith($index, $constraint);
        PHPUnitAssert::assertThat($array, $constraint, $message);
    }

    /**
     * Returns a new instance of the ArrayHasItemWith constraint.
     *
     * @param int        $index      the index of the item to check
     * @param Constraint $constraint the constraint the item's value is applied to
     *
     * @return ArrayHasItemWith
     */
    public static function arrayHasItemWith(int $index, Constraint $constraint): ArrayHasItemWith
    {
        return new ArrayHasItemWith($index, $constraint);
    }
}
