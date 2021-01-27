<?php
/**
 * PHPUnitArrayAssertions - Array-related PHPUnit assertions for API testing.
 *
 * @copyright Copyright (c) 2021, Daniel Rudolf (<https://www.daniel-rudolf.de>)
 *
 * This file is copyrighted by the contributors recorded in the version control
 * history of the file, available from the following original location:
 *
 * <https://github.com/PhrozenByte/phpunit-array-asserts/blob/master/tests/Utils/TestConstraint.php>
 *
 * @license http://opensource.org/licenses/MIT The MIT License
 *
 * SPDX-License-Identifier: MIT
 * License-Filename: LICENSE
 */

declare(strict_types=1);

namespace PhrozenByte\PHPUnitArrayAsserts\Tests\Utils;

use PHPUnit\Framework\Constraint\Constraint;

/**
 * TestConstraint is a simple fully configurable constraint for unit testing.
 */
class TestConstraint extends Constraint
{
    /** @var string */
    private $toString;

    /** @var bool */
    private $matches;

    /** @var int */
    private $count;

    /**
     * TestConstraint constructor.
     *
     * @param array{toString: string, matches: bool, count: int} $options
     */
    public function __construct(array $options = [])
    {
        $this->toString = $options['toString'] ?? '';
        $this->matches = $options['matches'] ?? false;
        $this->count = $options['count'] ?? 1;
    }

    /**
     * {@inheritDoc}
     */
    public function toString(): string
    {
        return $this->toString;
    }

    /**
     * {@inheritDoc}
     */
    protected function matches($other): bool
    {
        return $this->matches;
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return $this->count;
    }
}
