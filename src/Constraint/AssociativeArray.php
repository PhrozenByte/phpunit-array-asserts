<?php
/**
 * PHPUnitArrayAssertions - Array-related PHPUnit assertions for API testing.
 *
 * @copyright Copyright (c) 2020, Daniel Rudolf (<https://www.daniel-rudolf.de>)
 *
 * This file is copyrighted by the contributors recorded in the version control
 * history of the file, available from the following original location:
 *
 * <https://github.com/PhrozenByte/phpunit-array-asserts/blob/master/src/Constraint/AssociativeArray.php>
 *
 * @license http://opensource.org/licenses/MIT The MIT License
 *
 * SPDX-License-Identifier: MIT
 * License-Filename: LICENSE
 */

declare(strict_types=1);

namespace PhrozenByte\PHPUnitArrayAsserts\Constraint;

use ArrayAccess;
use LucidFrame\Console\ConsoleTable;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Exception as PHPUnitException;

/**
 * Constraint that asserts that a value is an associative array matching a
 * given structure and that the array's items pass other constraints.
 *
 * Any native array and ArrayAccess object is considered an associative array,
 * no matter which keys they use. However, the array's items are applied to
 * the matching constraint. By default, additional items will fail the
 * constraint. The same is true when items are missing.
 *
 * The expected keys and constraints to apply, as well as whether additional
 * and/or missing items should fail the constraint, are passed in the
 * constructor.
 */
class AssociativeArray extends Constraint
{
    /** @var Constraint[] */
    protected $constraints = [];

    /** @var bool */
    protected $allowAdditional;

    /** @var bool */
    protected $allowMissing;

    /**
     * AssociativeArray constructor.
     *
     * @param Constraint[] $constraints     an associative array with the expected keys and constraints to apply
     * @param bool         $allowAdditional whether additional items should fail the constraint (defaults to FALSE)
     * @param bool         $allowMissing    whether missing items should fail the constraint (defaults to FALSE)
     */
    public function __construct(array $constraints, bool $allowAdditional = false, bool $allowMissing = false)
    {
        foreach ($constraints as $key => $constraint) {
            if (!($constraint instanceof Constraint)) {
                $errorTemplate = 'All constraints of %s must be instances of %s.';
                throw new PHPUnitException(sprintf($errorTemplate, __CLASS__, Constraint::class));
            }

            $this->constraints[$key] = $constraint;
        }

        $this->allowAdditional = $allowAdditional;
        $this->allowMissing = $allowMissing;
    }

    /**
     * {@inheritDoc}
     */
    public function toString(): string
    {
        $constraintDescriptions = [];
        foreach ($this->constraints as $key => $constraint) {
            $constraintDescriptions[] = 'has the key ' . $this->exporter()->export($key) . ' '
                    . 'whose value ' . $constraint->toString();
        }

        return 'is an associative array that '
            . implode(!$this->allowMissing ? ' and ' : ' or ', $constraintDescriptions)
            . ($this->allowAdditional ? ' or any other item' : '');
    }

    /**
     * {@inheritDoc}
     */
    protected function matches($other): bool
    {
        foreach ($this->constraints as $key => $constraint) {
            $valueExists = false;
            if (is_array($other)) {
                $valueExists = array_key_exists($key, $other);
            } elseif ($other instanceof ArrayAccess) {
                $valueExists = $other->offsetExists($key);
            }

            if (!($valueExists ? $constraint->evaluate($other[$key], '', true) : $this->allowMissing)) {
                return false;
            }
        }

        if (!$this->allowAdditional) {
            return !array_diff_key($other, $this->constraints);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        $count = 1;
        foreach ($this->constraints as $constraint) {
            $count += count($constraint);
        }

        return $count;
    }

    /**
     * {@inheritDoc}
     */
    protected function failureDescription($other): string
    {
        if (!(is_array($other) || ($other instanceof ArrayAccess))) {
            return $this->exporter()->export($other) . ' is an associative array';
        }

        return 'associative array matches constraints';
    }

    /**
     * {@inheritDoc}
     */
    protected function additionalFailureDescription($other): string
    {
        if (!(is_array($other) || ($other instanceof ArrayAccess))) {
            return '';
        }

        $table = new ConsoleTable();
        $table->setHeaders([ 'Key', 'Value', 'Constraint' ]);

        foreach ($this->constraints as $key => $constraint) {
            $valueExists = false;
            if (is_array($other)) {
                $valueExists = array_key_exists($key, $other);
            } elseif ($other instanceof ArrayAccess) {
                $valueExists = $other->offsetExists($key);
            }

            $table->addRow([
                $this->exporter()->export($key),
                $valueExists ? $this->exporter()->shortenedExport($other[$key]) : '',
                'Value ' . $constraint->toString(),
            ]);
        }

        $output = $table->getTable();
        $output .= sprintf(
            "[%s] Allow additional; [%s] Allow missing\n",
            $this->allowAdditional ? 'x' : ' ',
            $this->allowMissing ? 'x' : ' '
        );

        return $output;
    }
}
