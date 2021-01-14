<?php
/**
 * PHPUnitArrayAssertions - Array-related PHPUnit assertions for API testing.
 *
 * @copyright Copyright (c) 2021, Daniel Rudolf (<https://www.daniel-rudolf.de>)
 *
 * This file is copyrighted by the contributors recorded in the version control
 * history of the file, available from the following original location:
 *
 * <https://github.com/PhrozenByte/phpunit-array-asserts/blob/master/tests/Unit/Constraint/SequentialArrayTest.php>
 *
 * @license http://opensource.org/licenses/MIT The MIT License
 *
 * SPDX-License-Identifier: MIT
 * License-Filename: LICENSE
 */

declare(strict_types=1);

namespace PhrozenByte\PHPUnitArrayAsserts\Tests\Utils;

use Iterator;
use IteratorAggregate;
use Traversable;

/**
 * IteratorAggregated is a simple implementation of the IteratorAggregate
 * interface.
 */
final class IteratorAggregated implements IteratorAggregate
{
    /** @var Iterator */
    private $iterator;

    /**
     * IteratorAggregated constructor.
     *
     * @param Iterator $iterator
     */
    public function __construct(Iterator $iterator)
    {
        $this->iterator = $iterator;
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): Traversable
    {
        return $this->iterator;
    }
}
