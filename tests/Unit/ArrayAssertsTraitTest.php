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

namespace PhrozenByte\PHPUnitArrayAsserts\Tests\Unit;

use ArrayAccess;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\InvalidArgumentException;
use PhrozenByte\PHPUnitArrayAsserts\ArrayAssertsTrait;
use PhrozenByte\PHPUnitArrayAsserts\Assert;
use PhrozenByte\PHPUnitArrayAsserts\Constraint\ArrayHasItemWith;
use PhrozenByte\PHPUnitArrayAsserts\Constraint\ArrayHasKeyWith;
use PhrozenByte\PHPUnitArrayAsserts\Constraint\AssociativeArray;
use PhrozenByte\PHPUnitArrayAsserts\Constraint\SequentialArray;
use PhrozenByte\PHPUnitArrayAsserts\Tests\TestCase;
use PhrozenByte\PHPUnitThrowableAsserts\CachedCallableProxy;
use Traversable;

/**
 * PHPUnit unit test for the ArrayAssertsTrait trait using the Assert class.
 *
 * This unit test uses Mockery instance mocking. This is affected by other unit
 * tests and will affect other unit tests. Thus we run all tests in separate
 * processes and without preserving the global state.
 *
 * @see ArrayAssertsTrait
 * @see Assert
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ArrayAssertsTraitTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @dataProvider dataProviderAssociativeArray
     *
     * @param Constraint[] $constraints
     * @param bool         $allowMissing
     * @param bool         $allowAdditional
     */
    public function testAssociativeArray(
        array $constraints,
        bool $allowMissing,
        bool $allowAdditional
    ): void {
        $this->mockConstraintInstance(
            AssociativeArray::class,
            [ $constraints, $allowMissing, $allowAdditional ]
        );

        $callableProxy = new CachedCallableProxy(
            [ Assert::class, 'associativeArray' ],
            $constraints,
            $allowMissing,
            $allowAdditional
        );

        $this->assertCallableThrowsNot($callableProxy, InvalidArgumentException::class);
        $this->assertInstanceOf(AssociativeArray::class, $callableProxy->getReturnValue());
    }

    /**
     * @dataProvider dataProviderAssociativeArray
     *
     * @param Constraint[]      $constraints
     * @param bool              $allowMissing
     * @param bool              $allowAdditional
     * @param array|ArrayAccess $array
     */
    public function testAssertAssociativeArray(
        array $constraints,
        bool $allowMissing,
        bool $allowAdditional,
        $array
    ): void {
        $this->mockConstraintInstance(
            AssociativeArray::class,
            [ $constraints, $allowMissing, $allowAdditional ],
            [ $array, '' ]
        );

        $this->assertCallableThrowsNot(
            $this->callableProxy(
                [ Assert::class, 'assertAssociativeArray' ],
                $constraints,
                $array,
                $allowMissing,
                $allowAdditional
            ),
            InvalidArgumentException::class
        );
    }

    /**
     * @return array[]
     */
    public function dataProviderAssociativeArray(): array
    {
        return $this->getTestDataSets('testAssociativeArray');
    }

    /**
     * @dataProvider dataProviderAssociativeArrayFail
     *
     * @param Constraint[]      $constraints
     * @param bool              $allowMissing
     * @param bool              $allowAdditional
     * @param array|ArrayAccess $array
     * @param string            $expectedException
     * @param string            $expectedExceptionMessage
     */
    public function testAssertAssociativeArrayFail(
        array $constraints,
        bool $allowMissing,
        bool $allowAdditional,
        $array,
        string $expectedException,
        string $expectedExceptionMessage
    ): void {
        $this->mockConstraintInstance(
            AssociativeArray::class,
            [ $constraints, $allowMissing, $allowAdditional ],
            [ $array, '' ]
        );

        $this->assertCallableThrows(
            $this->callableProxy(
                [ Assert::class, 'assertAssociativeArray' ],
                $constraints,
                $array,
                $allowMissing,
                $allowAdditional
            ),
            $expectedException,
            $expectedExceptionMessage
        );
    }

    /**
     * @return array[]
     */
    public function dataProviderAssociativeArrayFail(): array
    {
        return $this->getTestDataSets('testAssociativeArrayFail');
    }

    /**
     * @dataProvider dataProviderArrayHasKeyWith
     *
     * @param int|string $key
     * @param Constraint $constraint
     */
    public function testArrayHasKeyWith(
        $key,
        Constraint $constraint
    ): void {
        $this->mockConstraintInstance(
            ArrayHasKeyWith::class,
            [ $key, $constraint ]
        );

        $callableProxy = new CachedCallableProxy([ Assert::class, 'arrayHasKeyWith' ], $key, $constraint);
        $this->assertCallableThrowsNot($callableProxy, InvalidArgumentException::class);
        $this->assertInstanceOf(ArrayHasKeyWith::class, $callableProxy->getReturnValue());
    }

    /**
     * @dataProvider dataProviderArrayHasKeyWith
     *
     * @param int|string        $key
     * @param Constraint        $constraint
     * @param array|ArrayAccess $array
     */
    public function testAssertArrayHasKeyWith(
        $key,
        Constraint $constraint,
        $array
    ): void {
        $this->mockConstraintInstance(
            ArrayHasKeyWith::class,
            [ $key, $constraint ],
            [ $array, '' ]
        );

        $this->assertCallableThrowsNot(
            $this->callableProxy([ Assert::class, 'assertArrayHasKeyWith' ], $key, $constraint, $array),
            InvalidArgumentException::class
        );
    }

    /**
     * @return array[]
     */
    public function dataProviderArrayHasKeyWith(): array
    {
        return $this->getTestDataSets('testArrayHasKeyWith');
    }

    /**
     * @dataProvider dataProviderArrayHasKeyWithFail
     *
     * @param int|string        $key
     * @param Constraint        $constraint
     * @param array|ArrayAccess $array
     * @param string            $expectedException
     * @param string            $expectedExceptionMessage
     */
    public function testAssertArrayHasKeyWithFail(
        $key,
        Constraint $constraint,
        $array,
        string $expectedException,
        string $expectedExceptionMessage
    ): void {
        $this->mockConstraintInstance(
            ArrayHasKeyWith::class,
            [ $key, $constraint ],
            [ $array, '' ]
        );

        $this->assertCallableThrows(
            $this->callableProxy([ Assert::class, 'assertArrayHasKeyWith' ], $key, $constraint, $array),
            $expectedException,
            $expectedExceptionMessage
        );
    }

    /**
     * @return array[]
     */
    public function dataProviderArrayHasKeyWithFail(): array
    {
        return $this->getTestDataSets('testArrayHasKeyWithFail');
    }

    /**
     * @dataProvider dataProviderSequentialArray
     *
     * @param int               $minItems
     * @param int|null          $maxItems
     * @param Constraint|null   $constraint
     */
    public function testSequentialArray(
        int $minItems,
        ?int $maxItems,
        ?Constraint $constraint
    ): void {
        $this->mockConstraintInstance(
            SequentialArray::class,
            [ $minItems, $maxItems, $constraint ]
        );

        $callableProxy = new CachedCallableProxy(
            [ Assert::class, 'sequentialArray' ],
            $minItems,
            $maxItems,
            $constraint
        );

        $this->assertCallableThrowsNot($callableProxy, InvalidArgumentException::class);
        $this->assertInstanceOf(SequentialArray::class, $callableProxy->getReturnValue());
    }

    /**
     * @dataProvider dataProviderSequentialArray
     *
     * @param int               $minItems
     * @param int|null          $maxItems
     * @param Constraint|null   $constraint
     * @param array|Traversable $array
     */
    public function testAssertSequentialArray(
        int $minItems,
        ?int $maxItems,
        ?Constraint $constraint,
        $array
    ): void {
        $this->mockConstraintInstance(
            SequentialArray::class,
            [ $minItems, $maxItems, $constraint ],
            [ $array, '' ]
        );

        $this->assertCallableThrowsNot(
            $this->callableProxy([ Assert::class, 'assertSequentialArray' ], $array, $minItems, $maxItems, $constraint),
            InvalidArgumentException::class
        );
    }

    /**
     * @return array[]
     */
    public function dataProviderSequentialArray(): array
    {
        return $this->getTestDataSets('testSequentialArray');
    }

    /**
     * @dataProvider dataProviderSequentialArrayFail
     *
     * @param int               $minItems
     * @param int|null          $maxItems
     * @param Constraint|null   $constraint
     * @param array|Traversable $array
     * @param string            $expectedException
     * @param string            $expectedExceptionMessage
     */
    public function testAssertSequentialArrayFail(
        int $minItems,
        ?int $maxItems,
        ?Constraint $constraint,
        $array,
        string $expectedException,
        string $expectedExceptionMessage
    ): void {
        $this->mockConstraintInstance(
            SequentialArray::class,
            [ $minItems, $maxItems, $constraint ],
            [ $array, '' ]
        );

        $this->assertCallableThrows(
            $this->callableProxy([ Assert::class, 'assertSequentialArray' ], $array, $minItems, $maxItems, $constraint),
            $expectedException,
            $expectedExceptionMessage
        );
    }

    /**
     * @return array[]
     */
    public function dataProviderSequentialArrayFail(): array
    {
        return $this->getTestDataSets('testSequentialArrayFail');
    }

    /**
     * @dataProvider dataProviderArrayHasItemWith
     *
     * @param int        $index
     * @param Constraint $constraint
     */
    public function testArrayHasItemWith(
        int $index,
        Constraint $constraint
    ): void {
        $this->mockConstraintInstance(
            ArrayHasItemWith::class,
            [ $index, $constraint ]
        );

        $callableProxy = new CachedCallableProxy([ Assert::class, 'arrayHasItemWith' ], $index, $constraint);
        $this->assertCallableThrowsNot($callableProxy, InvalidArgumentException::class);
        $this->assertInstanceOf(ArrayHasItemWith::class, $callableProxy->getReturnValue());
    }

    /**
     * @dataProvider dataProviderArrayHasItemWith
     *
     * @param int               $index
     * @param Constraint        $constraint
     * @param array|Traversable $array
     */
    public function testAssertArrayHasItemWith(
        int $index,
        Constraint $constraint,
        $array
    ): void {
        $this->mockConstraintInstance(
            ArrayHasItemWith::class,
            [ $index, $constraint ],
            [ $array, '' ]
        );

        $this->assertCallableThrowsNot(
            $this->callableProxy([ Assert::class, 'assertArrayHasItemWith' ], $index, $constraint, $array),
            InvalidArgumentException::class
        );
    }

    /**
     * @return array[]
     */
    public function dataProviderArrayHasItemWith(): array
    {
        return $this->getTestDataSets('testArrayHasItemWith');
    }

    /**
     * @dataProvider dataProviderArrayHasItemWithFail
     *
     * @param int               $index
     * @param Constraint        $constraint
     * @param array|Traversable $array
     * @param string            $expectedException
     * @param string            $expectedExceptionMessage
     */
    public function testAssertArrayHasItemWithFail(
        int $index,
        Constraint $constraint,
        $array,
        string $expectedException,
        string $expectedExceptionMessage
    ): void {
        $this->mockConstraintInstance(
            ArrayHasItemWith::class,
            [ $index, $constraint ],
            [ $array, '' ]
        );

        $this->assertCallableThrows(
            $this->callableProxy([ Assert::class, 'assertArrayHasItemWith' ], $index, $constraint, $array),
            $expectedException,
            $expectedExceptionMessage
        );
    }

    /**
     * @return array[]
     */
    public function dataProviderArrayHasItemWithFail(): array
    {
        return $this->getTestDataSets('testArrayHasItemWithFail');
    }

    /**
     * @param string     $className
     * @param array      $constructorArguments
     * @param array|null $evaluateArguments
     *
     * @return MockInterface
     */
    private function mockConstraintInstance(
        string $className,
        array $constructorArguments = [],
        array $evaluateArguments = null
    ): MockInterface {
        $instanceMock = Mockery::mock('overload:' . $className, Constraint::class);

        $instanceMock->shouldReceive('__construct')
            ->with(...$constructorArguments)
            ->once();

        if ($evaluateArguments !== null) {
            $instanceMock->shouldReceive('evaluate')
                ->with(...$evaluateArguments)
                ->atMost()->once();
        } else {
            $instanceMock->shouldNotReceive('evaluate');
        }

        $instanceMock->shouldReceive([
            'count'    => 1,
            'toString' => 'is tested'
        ]);

        return $instanceMock;
    }
}
