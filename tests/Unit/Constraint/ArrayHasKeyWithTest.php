<?php
/**
 * PHPUnitArrayAssertions - Array-related PHPUnit assertions for API testing.
 *
 * @copyright Copyright (c) 2021, Daniel Rudolf (<https://www.daniel-rudolf.de>)
 *
 * This file is copyrighted by the contributors recorded in the version control
 * history of the file, available from the following original location:
 *
 * <https://github.com/PhrozenByte/phpunit-array-asserts/blob/master/tests/Unit/Constraint/ArrayHasKeyWithTest.php>
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
use PhrozenByte\PHPUnitArrayAsserts\Constraint\ArrayHasKeyWith;
use PhrozenByte\PHPUnitArrayAsserts\Tests\TestCase;
use SebastianBergmann\Exporter\Exporter;

/**
 * PHPUnit unit test for the ArrayHasKeyWith constraint.
 *
 * @see ArrayHasKeyWith
 *
 * @covers \PhrozenByte\PHPUnitArrayAsserts\Constraint\ArrayHasKeyWith
 */
class ArrayHasKeyWithTest extends TestCase
{
    /**
     * @dataProvider dataProviderInvalidParameters
     *
     * @param string|int $key
     * @param Constraint $constraint
     * @param string     $expectedException
     * @param string     $expectedExceptionMessage
     */
    public function testInvalidParameters(
        $key,
        Constraint $constraint,
        string $expectedException,
        string $expectedExceptionMessage
    ): void {
        $this->assertCallableThrows(static function () use ($key, $constraint) {
            new ArrayHasKeyWith($key, $constraint);
        }, $expectedException, $expectedExceptionMessage);
    }

    /**
     * @return array[]
     */
    public function dataProviderInvalidParameters(): array
    {
        return $this->getTestDataSets('testInvalidParameters');
    }

    /**
     * @dataProvider dataProviderSelfDescribing
     *
     * @param string|int $key
     * @param Constraint $constraint
     * @param string     $expectedDescription
     */
    public function testSelfDescribing($key, Constraint $constraint, string $expectedDescription): void
    {
        $mockedConstraint = $this->mockConstraint($constraint, [ 'toString' => $this->once() ]);

        $itemConstraint = new ArrayHasKeyWith($key, $mockedConstraint);
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
     * @param string|int $key
     * @param Constraint $constraint
     * @param mixed      $other
     * @param mixed      $expectedEvaluationValue
     */
    public function testEvaluate(
        $key,
        Constraint $constraint,
        $other,
        $expectedEvaluationValue
    ): void {
        $mockedConstraint = $this->mockConstraint(
            $constraint,
            [ 'evaluate' => $this->once() ],
            [ $expectedEvaluationValue, '', true ]
        );

        $itemConstraint = new ArrayHasKeyWith($key, $mockedConstraint);

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
     * @param string|int $key
     * @param Constraint $constraint
     * @param mixed      $other
     * @param mixed      $expectedEvaluationValue
     * @param string     $expectedExceptionMessage
     */
    public function testEvaluateFail(
        $key,
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

        $itemConstraint = new ArrayHasKeyWith($key, $mockedConstraint);

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
     * @param string|int $key
     * @param Constraint $constraint
     * @param mixed      $other
     * @param string     $expectedExceptionMessage
     */
    public function testPreEvaluateFail($key, Constraint $constraint, $other, string $expectedExceptionMessage): void
    {
        $mockedConstraint = $this->mockConstraint($constraint);

        $itemConstraint = new ArrayHasKeyWith($key, $mockedConstraint);

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
     * @param string|int $key
     * @param Constraint $constraint
     * @param int        $expectedCount
     */
    public function testCountable($key, Constraint $constraint, int $expectedCount): void
    {
        $mockedConstraint = $this->mockConstraint($constraint, [ 'count' => $this->once() ]);

        $itemConstraint = new ArrayHasKeyWith($key, $mockedConstraint);
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
