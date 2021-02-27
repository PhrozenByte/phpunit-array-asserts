<?php
/**
 * PHPUnitArrayAssertions - Array-related PHPUnit assertions for API testing.
 *
 * @copyright Copyright (c) 2021, Daniel Rudolf (<https://www.daniel-rudolf.de>)
 *
 * This file is copyrighted by the contributors recorded in the version control
 * history of the file, available from the following original location:
 *
 * <https://github.com/PhrozenByte/phpunit-array-asserts/blob/master/tests/Unit/Constraint/ArrayHasItemWithTest.php>
 *
 * @license http://opensource.org/licenses/MIT The MIT License
 *
 * SPDX-License-Identifier: MIT
 * License-Filename: LICENSE
 */

declare(strict_types=1);

namespace PhrozenByte\PHPUnitArrayAsserts\Tests\Unit\Constraint;

use ArrayIterator;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use PhrozenByte\PHPUnitArrayAsserts\Constraint\ArrayHasItemWith;
use PhrozenByte\PHPUnitArrayAsserts\Tests\TestCase;
use PhrozenByte\PHPUnitArrayAsserts\Tests\Utils\TestConstraint;
use SebastianBergmann\Exporter\Exporter;

/**
 * PHPUnit unit test for the ArrayHasItemWith constraint.
 *
 * @see ArrayHasItemWith
 *
 * @covers \PhrozenByte\PHPUnitArrayAsserts\Constraint\ArrayHasItemWith
 */
class ArrayHasItemWithTest extends TestCase
{
    /**
     * @dataProvider dataProviderSelfDescribing
     *
     * @param int              $index
     * @param Constraint|mixed $constraint
     * @param string           $expectedDescription
     */
    public function testSelfDescribing(int $index, $constraint, string $expectedDescription): void
    {
        $mockedConstraint = $this->mockConstraint($constraint, [ 'toString' => $this->once() ]);

        $itemConstraint = new ArrayHasItemWith($index, $mockedConstraint);
        $this->assertSame($expectedDescription, $itemConstraint->toString());
    }

    /**
     * @return array[]
     */
    public function dataProviderSelfDescribing(): array
    {
        return $this->getTestDataSets('testSelfDescribing');
    }

    /**
     * @dataProvider dataProviderEvaluate
     *
     * @param int              $index
     * @param Constraint|mixed $constraint
     * @param mixed            $other
     * @param mixed            $expectedEvaluationValue
     */
    public function testEvaluate(int $index, $constraint, $other, $expectedEvaluationValue): void
    {
        $mockedConstraint = $this->mockConstraint(
            $constraint,
            [ 'evaluate' => $this->once() ],
            [ $expectedEvaluationValue, '', true ]
        );

        $itemConstraint = new ArrayHasItemWith($index, $mockedConstraint);

        $this->assertCallableThrowsNot(
            $this->callableProxy([ $itemConstraint, 'evaluate' ], $other),
            ExpectationFailedException::class
        );
    }

    /**
     * @return array
     */
    public function dataProviderEvaluate(): array
    {
        return $this->getTestDataSets('testEvaluate');
    }

    /**
     * @dataProvider dataProviderEvaluateFail
     *
     * @param int              $index
     * @param Constraint|mixed $constraint
     * @param mixed            $other
     * @param mixed            $expectedEvaluationValue
     * @param string           $expectedExceptionMessage
     */
    public function testEvaluateFail(
        int $index,
        $constraint,
        $other,
        $expectedEvaluationValue,
        string $expectedExceptionMessage
    ): void {
        $mockedConstraint = $this->mockConstraint(
            $constraint,
            [ 'evaluate' => $this->once() ],
            [ $expectedEvaluationValue, '', true ]
        );

        $itemConstraint = new ArrayHasItemWith($index, $mockedConstraint);

        $this->assertCallableThrows(
            $this->callableProxy([ $itemConstraint, 'evaluate' ], $other),
            ExpectationFailedException::class,
            sprintf($expectedExceptionMessage, (new Exporter())->export($other))
        );
    }

    /**
     * @return array
     */
    public function dataProviderEvaluateFail(): array
    {
        return $this->getTestDataSets('testEvaluateFail');
    }

    /**
     * @dataProvider dataProviderPreEvaluateFail
     *
     * @param int              $index
     * @param Constraint|mixed $constraint
     * @param mixed            $other
     * @param string           $expectedExceptionMessage
     */
    public function testPreEvaluateFail(int $index, $constraint, $other, string $expectedExceptionMessage): void
    {
        $mockedConstraint = $this->mockConstraint($constraint);

        $itemConstraint = new ArrayHasItemWith($index, $mockedConstraint);

        $this->assertCallableThrows(
            $this->callableProxy([ $itemConstraint, 'evaluate' ], $other),
            ExpectationFailedException::class,
            sprintf($expectedExceptionMessage, (new Exporter())->export($other))
        );
    }

    /**
     * @return array
     */
    public function dataProviderPreEvaluateFail(): array
    {
        return $this->getTestDataSets('testPreEvaluateFail');
    }

    public function testIteratorWithIntermediatePointer(): void
    {
        $itemConstraint = new ArrayHasItemWith(2, new TestConstraint([ 'matches' => true ]));
        $other = new class extends ArrayIterator {
            public function __construct()
            {
                parent::__construct([ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10 ]);

                // move pointer after item #4
                foreach ($this as $value) {
                    if ($value === 4) {
                        break;
                    }
                }
            }
        };

        $this->assertCallableThrowsNot(
            $this->callableProxy([ $itemConstraint, 'evaluate' ], $other),
            ExpectationFailedException::class
        );

        $this->assertTrue($other->valid());
        $this->assertSame(4, $other->current());
    }

    public function testGeneratorWithIntermediatePointer(): void
    {
        $expectedException = ExpectationFailedException::class;
        $expectedExceptionMessage = 'Failed asserting that %s is an array that has a value at index 2 which exists.';

        $itemConstraint = new ArrayHasItemWith(2, new TestConstraint([ 'toString' => 'exists' ]));
        $other = (function () {
            for ($i = 1; $i <= 10; $i++) {
                yield $i;
            }
        })();

        // move pointer after item #2
        $other->next();
        $other->next();

        $this->assertCallableThrows(
            $this->callableProxy([ $itemConstraint, 'evaluate' ], $other),
            $expectedException,
            sprintf($expectedExceptionMessage, (new Exporter())->export($other))
        );
    }

    /**
     * @dataProvider dataProviderCountable
     *
     * @param int              $index
     * @param Constraint|mixed $constraint
     * @param int              $expectedCount
     */
    public function testCountable(int $index, $constraint, int $expectedCount): void
    {
        $mockedConstraint = $this->mockConstraint($constraint, [ 'count' => $this->once() ]);

        $itemConstraint = new ArrayHasItemWith($index, $mockedConstraint);
        $this->assertSame($expectedCount, $itemConstraint->count());
    }

    /**
     * @return array[]
     */
    public function dataProviderCountable(): array
    {
        return $this->getTestDataSets('testCountable');
    }
}
