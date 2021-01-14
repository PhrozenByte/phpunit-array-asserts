PHPUnitArrayAssertions
======================

[`PHPUnitArrayAssertions`](https://github.com/PhrozenByte/phpunit-array-asserts) is a small [PHPUnit](https://phpunit.de/) extension to improve testing of PHP arrays and array-like data. It introduces the [`AssociativeArray`](#constraint-associativearray), [`ArrayHasKeyWith`](#constraint-arrayhaskeywith), [`SequentialArray`](#constraint-sequentialarray), and [`ArrayHasItemWith`](#constraint-arrayhasitemwith) constraints. It is often used for API testing to assert whether an API result matches certain criteria - regarding both its structure, and the data.

This PHPUnit extension allows developers to test structure and data in single assertion, making test cases less repetitive and easier to understand. In some way it's an alternative to PHPUnit's `ArraySubset` constraint that was deprecated in PHPUnit 8 and removed in PHPUnit 9 - just way more powerful and less confusing. Refer to the ["Usage" section](#usage) and ["Example" section](#example) below for more info.

You want more PHPUnit constraints? Check out [`PHPUnitThrowableAssertions`](https://github.com/PhrozenByte/phpunit-throwable-asserts)! It introduces the `assertCallableThrows()` and `assertCallableThrowsNot()` assertions to improve testing of exceptions and PHP errors. It's more powerful and flexible than PHPUnit's core `expectException()` and `expectError()` methods.

Made with :heart: by [Daniel Rudolf](https://www.daniel-rudolf.de). `PHPUnitArrayAssertions` is free and open source software, released under the terms of the [MIT license](https://github.com/PhrozenByte/phpunit-array-asserts/blob/master/LICENSE).

**Table of contents:**

1. [Install](#install)
2. [Usage](#usage)
    1. [Constraint `AssociativeArray`](#constraint-associativearray)
    2. [Constraint `ArrayHasKeyWith`](#constraint-arrayhaskeywith)
    3. [Constraint `SequentialArray`](#constraint-sequentialarray)
    4. [Constraint `ArrayHasItemWith`](#constraint-arrayhasitemwith)
3. [Example](#example)

Install
-------

`PHPUnitArrayAssertions` is available on [Packagist.org](https://packagist.org/packages/phrozenbyte/phpunit-array-asserts) and can be installed using [Composer](https://getcomposer.org/):

```shell
composer require --dev phrozenbyte/phpunit-array-asserts
```

This PHPUnit extension was initially written for PHPUnit 8, but should work fine with any later PHPUnit version. If it doesn't, please don't hesitate to open a [new Issue on GitHub](https://github.com/PhrozenByte/phpunit-array-asserts/issues/new), or, even better, create a Pull Request with a proposed fix.

Usage
-----

There are three (basically equivalent) options to use `PHPUnitArrayAssertions`:

- By using the static [class `PhrozenByte\PHPUnitArrayAsserts\Assert`](https://github.com/PhrozenByte/phpunit-array-asserts/blob/master/src/Assert.php)
- By using the [trait `PhrozenByte\PHPUnitArrayAsserts\ArrayAssertsTrait`](https://github.com/PhrozenByte/phpunit-array-asserts/blob/master/src/ArrayAssertsTrait.php) in your test case
- By creating new constraint instances (`PhrozenByte\PHPUnitArrayAsserts\Constraint\…`)

All options do the same, the only difference is that the static class and trait both throw `PHPUnit\Framework\InvalidArgumentException` exceptions for invalid parameters. Creating new constraint instances is useful for advanced assertions, e.g. together with `PHPUnit\Framework\Constraint\LogicalAnd`.

### Constraint `AssociativeArray`

The [`AssociativeArray` constraint](https://github.com/PhrozenByte/phpunit-array-asserts/blob/master/src/Constraint/AssociativeArray.php) asserts that a value is an associative array matching a given structure and that the array's items pass other constraints.

Any native array and `ArrayAccess` object is considered an associative array, no matter which keys they use. However, the array's items are applied to the matching constraint (parameter `$consotraints`). By default, missing items will fail the constraint (parameter `$allowMissing`, defaults to `false`). Additional items will be ignored by default (parameter `$allowAdditional`, defaults to `true`). If you want the constraint to fail when additional items exist, set this option to `true`, however, please note that this works for native arrays only. The expected keys and constraints to apply, as well as whether missing and/or additional items should fail the constraint, are passed in the constructor.

**Usage:**

```php
// using `\PhrozenByte\PHPUnitArrayAsserts\ArrayAssertsTrait` trait
self::assertAssociativeArray(
    array $constraints,            // an associative array with the expected keys and constraints to apply
    array|ArrayAccess $array,      // the associative array to check
    bool $allowMissing = false,    // whether missing items should fail the constraint
    bool $allowAdditional = true,  // whether additional items should fail the constraint
    string $message = ''           // additional information about the test
);

// using new instance of `\PhrozenByte\PHPUnitArrayAsserts\Constraint\AssociativeArray`
new AssociativeArray(
    array $constraints,
    bool $allowMissing = false,
    bool $allowAdditional = true
);
```

**Example:**

```php
$data = [
    'id'      => 42,
    'name'    => 'Arthur Dent',
    'options' => [ 'has_towel' => true, 'panic' => false ],
];

// asserts that `$data` is an associative array with exactly the keys:
//     - "id" with a numeric value,
//     - "name" with the value "Arthur Dent", and
//     - "options" with another associative array with the key "panic", whose value must be a boolean
$this->assertAssociativeArray([
    'id'      => $this->isType(IsType::TYPE_INT),
    'name'    => $this->identicalTo('Arthur Dent'),
    'options' => $this->associativeArray([ 'panic' => $this->isType(IsType::TYPE_BOOL) ], true)
], $data);
```

**Debugging:**

```php
$data = [
    'answer' => 21 /* half the truth */
];

$this->assertAssociativeArray([
    'answer' => $this->identicalTo(42)
], $data);

// Will fail with the following message:
//
//     Failed asserting that associative array matches constraints.
//     +----------+-------+--------------------------+
//     | Key      | Value | Constraint               |
//     +----------+-------+--------------------------+
//     | 'answer' | 21    | Value is identical to 42 |
//     +----------+-------+--------------------------+
//     [ ] Allow missing; [x] Allow additional
```

### Constraint `ArrayHasKeyWith`

The [`ArrayHasKeyWith` constraint](https://github.com/PhrozenByte/phpunit-array-asserts/blob/master/src/Constraint/ArrayHasKeyWith.php) asserts that an array has a given key and that its value passes another constraint.

Accepts both native arrays and `ArrayAccess` objects. The constraint (parameter `$constraint`) will fail if the key (parameter `$key`) doesn't exist in the array. The item's key and the constraint the value must pass are passed in the constructor.

**Usage:**

```php
// using `\PhrozenByte\PHPUnitArrayAsserts\ArrayAssertsTrait` trait
self::assertArrayHasKeyWith(
    string|int $key,          // the key of the item to check
    Constraint $constraint,   // the constraint the item's value is applied to
    array|ArrayAccess $array, // the array to check
    string $message = ''      // additional information about the test
);

// using new instance of `\PhrozenByte\PHPUnitArrayAsserts\Constraint\ArrayHasKeyWith`
new ArrayHasKeyWith(
    string|int $key,
    Constraint $constraint
);
```

**Example:**

```php
$data = [
    'id'      => 42,
    'name'    => 'Arthur Dent',
    'options' => [ 'has_towel' => true, 'panic' => false ],
];

// asserts that $data has the item `name` with the value "Arthur Dent"
$this->assertArrayHasKeyWith('name', $this->identicalTo('Arthur Dent'), $data);
```

**Debugging:**

```php
$data = [];

$this->assertArrayHasKeyWith('answer', $this->identicalTo(42), $data);

// Will fail with the following message:
//
//     Failed asserting that Array &0 () is an array that
//     has the key 'answer' whose value is identical to 42.
```

### Constraint `SequentialArray`

The [`SequentialArray` constraint](https://github.com/PhrozenByte/phpunit-array-asserts/blob/master/src/Constraint/SequentialArray.php) asserts that a value is like a sequential array, has a minimum and/or maximum number of items, and that all items pass another constraint.

Sequential arrays are defined as ordered lists with incrementing numeric keys starting from zero. This is especially true for native sequential arrays like `[ "foo", "bar" ]`. Empty arrays are considered valid, too. `Traversable` objects must have sequential keys to be considered valid. The expected minimum (parameter `$minItems`, defaults to `0`) and/or maximum (parameter `$maxItems`, defaults to `null`, meaning infinite) number of items, and the constraint to apply all items to (optional parameter `$constraint`), are passed in the constructor.

This constraint will fully traverse any `Traversable` object given. This also means that any `Generator` will be fully exhausted. If possible, it will try to restore an `Iterator`'s pointer to its previous state.

**Usage:**

```php
// using `\PhrozenByte\PHPUnitArrayAsserts\ArrayAssertsTrait` trait
self::assertSequentialArray(
    array|Traversable $array,      // the sequential array to check
    int $minItems,                 // required minimum number of items
    int $maxItems = null,          // required maximum number of items (pass null for infinite)
    Constraint $constraint = null, // optional constraint to apply all items to
    string $message = ''           // additional information about the test
);

// using new instance of `\PhrozenByte\PHPUnitArrayAsserts\Constraint\SequentialArray`
new SequentialArray(
    int $minItems = 0,
    int $maxItems = null,
    Constraint $constraint = null
);
```

**Example:**

```php
$data = [
    "The Hitchhiker's Guide to the Galaxy",
    "The Restaurant at the End of the Universe",
    "Life, the Universe and Everything",
    "So Long, and Thanks for All the Fish",
    "Mostly Harmless",
    "And Another Thing...",
];

// asserts that `$data` is a non-empty sequential array with non-empty items
$this->assertSequentialArray($data, 1, null, $this->logicalNot($this->isEmpty()));
```

**Debugging:**

```php
$data = [];

$this->assertSequentialArray($data, 4, null, $this->is(IsType::TYPE_STRING));

// Will fail with the following message:
//
//     Failed asserting that Array &0 () is is a sequential array
//     with ≥ 4 items matching the constraint "is of type "string"".
```

### Constraint `ArrayHasItemWith`

The [`ArrayHasItemWith` constraint](https://github.com/PhrozenByte/phpunit-array-asserts/blob/master/src/Constraint/ArrayHasItemWith.php) asserts that an array has a item at a given index and that its value passes another constraint.

Accepts both native arrays and `Traversable` objects. The constraint will fail if the array has less items than required. The index of the item to check (parameter `$index`), and the constraint its value must pass (parameter `$constraint`) are passed in the constructor.

This constraint will fully traverse any `Traversable` object given. This also means that any `Generator` will be fully exhausted. It doesn't restore an `Iterator`'s pointer to its previous state.

**Usage:**

```php
// using `\PhrozenByte\PHPUnitArrayAsserts\ArrayAssertsTrait` trait
self::assertArrayHasItemWith(
    int $index,               // the index of the item to check
    Constraint $constraint,   // the constraint the item's value is applied to
    array|Traversable $array, // the array to check
    string $message = ''      // additional information about the test
);

// using new instance of `\PhrozenByte\PHPUnitArrayAsserts\Constraint\ArrayHasItemWith`
new ArrayHasItemWith(
    int $index,
    Constraint $constraint
);
```

**Example:**

```php
$data = [
    '1979-10-12' => "The Hitchhiker's Guide to the Galaxy",
    '1980-10-00' => "The Restaurant at the End of the Universe",
    '1982-08-00' => "Life, the Universe and Everything",
    '1984-11-09' => "So Long, and Thanks for All the Fish",
    '1992-00-00' => "Mostly Harmless",
    '2009-10-12' => "And Another Thing...",
];

// asserts that `$data` contains "Life, the Universe and Everything" as third item (i.e. at index 2)
$this->assertArrayHasItemWith(2, $this->identicalTo("Life, the Universe and Everything"));
```

**Debugging:**

```php
$data = [];

$this->assertArrayHasItemWith(2, $this->identicalTo('Arthur Dent'), $data);

// Will fail with the following message:
//
//     Failed asserting that Array &0 () is an array that
//     has a value at index 2 which is identical to 'Arthur Dent'.
```

Example
-------

Here's a (more or less) real-world example of `PHPUnitArrayAssertions`. Check out the `testWithPHPUnitArrayAsserts()` method to see how a complex API response is tested. For a comparison with an implementation utilizing just PHPUnit's core features, check out the `testWithoutPHPUnitArrayAsserts()` method. Without `PHPUnitArrayAssertions` you end up having 17 lines of pretty repetitive code, with this PHPUnit extension you can test the response with 7 lines of easy to understand code.

```php
<?php
declare(strict_types=1);

namespace YourName\YourProject\Tests;

use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\TestCase;
use PhrozenByte\PHPUnitArrayAsserts\ArrayAssertsTrait;

class MyTest extends TestCase
{
    use ArrayAssertsTrait;

    public function testWithPHPUnitArrayAsserts(): void
    {
        // 7 lines of easy to understand code to check the API response *with* PHPUnitArrayAsserts

        // implement your test, the result is stored in $responseData

        $responseData = [
            'users' => [
                [
                    'id'      => 42,
                    'name'    => 'Arthur Dent',
                    'options' => [ 'has_towel' => true, 'panic' => false ],
                ],
            ]
        ];

        $this->assertArrayHasKeyWith('users', $this->sequentialArray(1), $responseData);

        $this->assertAssociativeArray([
            'id'      => $this->isType(IsType::TYPE_INT),
            'name'    => $this->identicalTo('Arthur Dent'),
            'options' => $this->associativeArray([ 'panic' => $this->isType(IsType::TYPE_BOOL) ])
        ], $responseData['users'][0]);
    }
    
    public function testWithoutPHPUnitArrayAsserts(): void
    {
        // 17 lines of pretty repetitive code to check the API response *without* PHPUnitArrayAsserts

        // implement your test, the result is stored in $responseData

        $responseData = [
            'users' => [
                [
                    'id'      => 42,
                    'name'    => 'Arthur Dent',
                    'options' => [ 'has_towel' => true, 'panic' => false ],
                ],
            ]
        ];

        $this->assertArrayHasKey('users', $responseData);
        $this->assertIsArray($responseData['users']);
        $this->assertGreaterThanOrEqual(1, count($responseData['users'])); // won't work for Traversable

        $userData = $responseData['users'][0]; // we can't really rely on the existence of key "0" here :/

        $this->assertArrayHasKey('id', $userData);
        $this->assertIsInt($userData['id']);

        $this->assertArrayHasKey('name', $userData);
        $this->assertSame('Arthur Dent', $userData['name']);

        $this->assertArrayHasKey('options', $userData);
        $this->assertIsArray($userData['options']);

        $this->assertArrayHasKey('panic', $userData['options']);
        $this->assertIsBool($userData['options']['panic']);
    }
}
```
