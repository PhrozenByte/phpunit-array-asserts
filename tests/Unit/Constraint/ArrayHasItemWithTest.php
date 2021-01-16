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

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use PhrozenByte\PHPUnitArrayAsserts\Constraint\ArrayHasItemWith;
use PhrozenByte\PHPUnitArrayAsserts\Tests\TestCase;
use SebastianBergmann\Exporter\Exporter;

/**
 * PHPUnit unit test for the ArrayHasItemWith constraint.
 *
 * @see ArrayHasItemWith
 */
class ArrayHasItemWithTest extends TestCase
{
    /**
     * @dataProvider dataProviderSelfDescribing
     *
     * @param int        $index
     * @param Constraint $constraint
     * @param string     $expectedDescription
     */
    public function testSelfDescribing(int $index, Constraint $constraint, string $expectedDescription): void
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
     * @param int        $index
     * @param Constraint $constraint
     * @param mixed      $other
     * @param mixed      $expectedEvaluationValue
     */
    public function testEvaluate(
        int $index,
        Constraint $constraint,
        $other,
        $expectedEvaluationValue
    ): void {
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
     * @param int        $index
     * @param Constraint $constraint
     * @param mixed      $other
     * @param mixed      $expectedEvaluationValue
     * @param string     $expectedExceptionMessage
     */
    public function testEvaluateFail(
        int $index,
        Constraint $constraint,
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
     * @param int        $index
     * @param Constraint $constraint
     * @param mixed      $other
     * @param string     $expectedExceptionMessage
     */
    public function testPreEvaluateFail(
        int $index,
        Constraint $constraint,
        $other,
        string $expectedExceptionMessage
    ): void {
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

    /**
     * @dataProvider dataProviderCountable
     *
     * @param int        $index
     * @param Constraint $constraint
     * @param int        $expectedCount
     */
    public function testCountable(int $index, Constraint $constraint, int $expectedCount): void
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
