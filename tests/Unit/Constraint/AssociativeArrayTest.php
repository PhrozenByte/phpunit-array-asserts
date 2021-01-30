<?php
/**
 * PHPUnitArrayAssertions - Array-related PHPUnit assertions for API testing.
 *
 * @copyright Copyright (c) 2021, Daniel Rudolf (<https://www.daniel-rudolf.de>)
 *
 * This file is copyrighted by the contributors recorded in the version control
 * history of the file, available from the following original location:
 *
 * <https://github.com/PhrozenByte/phpunit-array-asserts/blob/master/tests/Unit/Constraint/AssociativeArrayTest.php>
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
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use PhrozenByte\PHPUnitArrayAsserts\Constraint\AssociativeArray;
use PhrozenByte\PHPUnitArrayAsserts\Tests\TestCase;

/**
 * PHPUnit unit test for the AssociativeArray constraint.
 *
 * @see AssociativeArray
 *
 * @covers \PhrozenByte\PHPUnitArrayAsserts\Constraint\AssociativeArray
 */
class AssociativeArrayTest extends TestCase
{
    /**
     * @dataProvider dataProviderSelfDescribing
     *
     * @param Constraint[]|mixed[] $constraints
     * @param bool                 $allowMissing
     * @param bool                 $allowAdditional
     * @param string               $expectedDescription
     */
    public function testSelfDescribing(
        array $constraints,
        bool $allowMissing,
        bool $allowAdditional,
        string $expectedDescription
    ): void {
        $mockedConstraints = $this->mockConstraints($constraints, [ 'toString' => $this->once() ]);

        $itemConstraint = new AssociativeArray($mockedConstraints, $allowMissing, $allowAdditional);
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
     * @param Constraint[]|mixed[] $constraints
     * @param bool                 $allowMissing
     * @param bool                 $allowAdditional
     * @param mixed                $other
     * @param mixed[]              $expectedEvaluationValues
     */
    public function testEvaluate(
        array $constraints,
        bool $allowMissing,
        bool $allowAdditional,
        $other,
        array $expectedEvaluationValues
    ): void {
        $mockedConstraints = [];
        foreach ($constraints as $key => $constraint) {
            if (!isset($expectedEvaluationValues[$key])) {
                $mockedConstraints[$key] = $this->mockConstraint($constraint);
            } else {
                $mockedConstraints[$key] = $this->mockConstraint(
                    $constraint,
                    [ 'evaluate' => $this->once() ],
                    [ $expectedEvaluationValues[$key], '', true ]
                );
            }
        }

        $itemConstraint = new AssociativeArray($mockedConstraints, $allowMissing, $allowAdditional);

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
     * @param Constraint[]|mixed[] $constraints
     * @param bool                 $allowMissing
     * @param bool                 $allowAdditional
     * @param mixed                $other
     * @param mixed[]              $expectedEvaluationValues
     * @param string               $expectedExceptionMessage
     */
    public function testEvaluateFail(
        array $constraints,
        bool $allowMissing,
        bool $allowAdditional,
        $other,
        array $expectedEvaluationValues,
        string $expectedExceptionMessage
    ): void {
        $mockedConstraints = [];
        foreach ($constraints as $key => $constraint) {
            if (!isset($expectedEvaluationValues[$key])) {
                $mockedConstraints[$key] = $this->mockConstraint($constraint);
            } else {
                $mockedConstraints[$key] = $this->mockConstraint(
                    $constraint,
                    [ 'evaluate' => $this->once() ],
                    [ $expectedEvaluationValues[$key], '', true ]
                );
            }
        }

        $itemConstraint = new AssociativeArray($mockedConstraints, $allowMissing, $allowAdditional);

        $this->assertCallableThrows(
            $this->callableProxy([ $itemConstraint, 'evaluate' ], $other),
            ExpectationFailedException::class,
            $expectedExceptionMessage
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
     * @param Constraint[]|mixed[] $constraints
     * @param bool                 $allowMissing
     * @param bool                 $allowAdditional
     * @param mixed                $other
     * @param string               $expectedExceptionMessage
     */
    public function testPreEvaluateFail(
        array $constraints,
        bool $allowMissing,
        bool $allowAdditional,
        $other,
        string $expectedExceptionMessage
    ): void {
        $mockedConstraints = $this->mockConstraints($constraints, [ 'evaluate' => $this->atMost(1) ]);

        $itemConstraint = new AssociativeArray($mockedConstraints, $allowMissing, $allowAdditional);

        $this->assertCallableThrows(
            $this->callableProxy([ $itemConstraint, 'evaluate' ], $other),
            ExpectationFailedException::class,
            $expectedExceptionMessage
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
     * @param Constraint[]|mixed[] $constraints
     * @param bool                 $allowMissing
     * @param bool                 $allowAdditional
     * @param int                  $expectedCount
     */
    public function testCountable(
        array $constraints,
        bool $allowMissing,
        bool $allowAdditional,
        int $expectedCount
    ): void {
        $mockedConstraints = $this->mockConstraints($constraints, [ 'count' => $this->once() ]);

        $itemConstraint = new AssociativeArray($mockedConstraints, $allowMissing, $allowAdditional);
        $this->assertSame($expectedCount, $itemConstraint->count());
    }

    /**
     * @return array[]
     */
    public function dataProviderCountable(): array
    {
        return $this->getTestDataSets('testCountable');
    }

    /**
     * @param Constraint[]|mixed[] $constraints
     * @param InvocationOrder[]    $invocationRules
     * @param mixed[][]            $evaluateParameters
     *
     * @return Constraint[]
     */
    protected function mockConstraints(
        array $constraints,
        array $invocationRules = [],
        array $evaluateParameters = []
    ): array {
        $mockedConstraints = [];
        foreach ($constraints as $key => $constraint) {
            $constraintInvocationRules = [];
            foreach ($invocationRules as $method => $invocationRule) {
                $constraintInvocationRules[$method] = clone $invocationRule;
            }

            $mockedConstraints[$key] = $this->mockConstraint(
                $constraint,
                $constraintInvocationRules,
                $evaluateParameters[$key] ?? null
            );
        }

        return $mockedConstraints;
    }
}
