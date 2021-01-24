<?php
/**
 * PHPUnitArrayAssertions - Array-related PHPUnit assertions for API testing.
 *
 * @copyright Copyright (c) 2021, Daniel Rudolf (<https://www.daniel-rudolf.de>)
 *
 * This file is copyrighted by the contributors recorded in the version control
 * history of the file, available from the following original location:
 *
 * <https://github.com/PhrozenByte/phpunit-array-asserts/blob/master/tests/TestCase.php>
 *
 * @license http://opensource.org/licenses/MIT The MIT License
 *
 * SPDX-License-Identifier: MIT
 * License-Filename: LICENSE
 */

declare(strict_types=1);

namespace PhrozenByte\PHPUnitArrayAsserts\Tests;

use Generator;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Exception as PHPUnitException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use PhrozenByte\PHPUnitThrowableAsserts\ThrowableAssertsTrait;
use ReflectionObject;
use Symfony\Component\Yaml\Exception\ParseException as YamlParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Abstract TestCase for PHPUnitArrayAsserts unit tests providing helper
 * methods to mock constraints and to read YAML test data files.
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    use ThrowableAssertsTrait;

    /** @var array[][][] */
    protected static $testDataSets = [];

    /**
     * Mocks a constraint.
     *
     * @param Constraint        $constraint         the original constraint
     * @param InvocationOrder[] $invocationRules    invocation rules for public methods
     * @param mixed[]|null      $evaluateParameters the expected arguments passed to the `evaluate()` method
     *
     * @return Constraint
     */
    protected function mockConstraint(
        Constraint $constraint,
        array $invocationRules = [],
        array $evaluateParameters = null
    ): Constraint {
        /** @var Constraint|MockObject $mockedConstraint */
        $mockedConstraint = $this->getMockBuilder(get_class($constraint))
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->disableAutoReturnValueGeneration()
            ->onlyMethods([ 'toString', 'evaluate', 'count' ])
            ->getMock();

        $mockedConstraint
            ->expects($invocationRules['toString'] ?? $this->any())
            ->method('toString')
            ->willReturnCallback([ $constraint, 'toString' ]);

        if (isset($invocationRules['evaluate']) && ($evaluateParameters !== null)) {
            $mockedConstraint
                ->expects($invocationRules['evaluate'])
                ->method('evaluate')
                ->with(...$evaluateParameters)
                ->willReturnCallback([ $constraint, 'evaluate' ]);
        } else {
            $mockedConstraint
                ->expects($invocationRules['evaluate'] ?? $this->never())
                ->method('evaluate')
                ->willReturnCallback([ $constraint, 'evaluate' ]);
        }

        $mockedConstraint
            ->expects($invocationRules['count'] ?? $this->any())
            ->method('count')
            ->willReturnCallback([ $constraint, 'count' ]);

        return $mockedConstraint;
    }

    /**
     * Returns test data sets for a particular test. The test data sets are
     * stored in YAML files matching the test's class name.
     *
     * @param string $testName name of the test
     *
     * @return array[] test data sets
     */
    protected function getTestDataSets(string $testName): array
    {
        $error = null;
        $testClassName = (new ReflectionObject($this))->getShortName();
        $testDatasetsFile = __DIR__ . '/data/' . $testClassName . '.yml';

        if (!isset(self::$testDataSets[$testClassName])) {
            if (!file_exists($testDatasetsFile)) {
                $error = 'No such file or directory';
            } elseif (!is_file($testDatasetsFile)) {
                $error = 'Not a file';
            } elseif (!is_readable($testDatasetsFile)) {
                $error = 'Permission denied';
            } else {
                try {
                    self::$testDataSets[$testClassName] = $this->parseYaml(file_get_contents($testDatasetsFile));
                } catch (YamlParseException $e) {
                    $error = sprintf('YAML parse error: %s', $e->getMessage());
                }
            }
        }

        if (!isset(self::$testDataSets[$testClassName][$testName])) {
            if ($error === null) {
                $error = sprintf('Dataset "%s" not found', $testName);
            }
        }

        if ($error !== null) {
            $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

            throw new PHPUnitException(sprintf(
                'Test data file "%s" for %s::%s() (%s data sets) is invalid: %s',
                $testDatasetsFile,
                $stack[1]['class'],
                $stack[1]['function'],
                $testClassName,
                $error
            ));
        }

        return self::$testDataSets[$testClassName][$testName];
    }

    /**
     * Parses a YAML string.
     *
     * @param string $input YAML string
     *
     * @return mixed parsed data
     *
     * @throws YamlParseException
     */
    private function parseYaml(string $input)
    {
        $yaml = Yaml::parse($input);

        if (isset($yaml['~anchors'])) {
            unset($yaml['~anchors']);
        }

        $parseRecursive = static function ($value) use (&$parseRecursive) {
            if (is_array($value)) {
                if (isset($value['<<<'])) {
                    $mergeValues = $value['<<<'];
                    unset($value['<<<']);

                    if (is_array($mergeValues) && $mergeValues) {
                        if (!isset($mergeValues[0]) || ($mergeValues !== array_values($mergeValues))) {
                            $mergeValues = [ $mergeValues ];
                        }

                        foreach ($mergeValues as $mergeValue) {
                            $value = array_replace_recursive($value, $mergeValue);
                        }
                    }
                }

                if (isset($value['~generator'])) {
                    $options = $value['~generator'];
                    $generator = static function (int $start, int $step, int $stop): Generator {
                        for ($i = $start; $i <= $stop; $i += $step) {
                            yield $i;
                        }
                    };

                    return $generator($options['start'] ?? 0, $options['step'] ?? 1, $options['stop'] ?? 9);
                }

                if (isset($value['~object'])) {
                    $className = $value['~object'];
                    unset($value['~object']);

                    $parameters = array_values(array_map($parseRecursive, $value));
                    return new $className(...$parameters);
                }

                return array_map($parseRecursive, $value);
            }

            return $value;
        };

        return $parseRecursive($yaml);
    }
}
