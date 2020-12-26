PHPUnitArrayAssertions
======================

`PHPUnitArrayAssertions` is a small [PHPUnit](https://phpunit.de/) extension to improve testing of PHP arrays and array-like data. It introduces the `AssociativeArray`, `ArrayHasKeyWith`, `SequentialArray`, and `ArrayHasItemWith` constraints. It is often used for API testing to assert whether an API result matches certain criteria - regarding both its structure, and the data.

This PHPUnit extension allows developers to test structure and data in single assertion, making test cases less repetitive and easier to understand. In some way it's an alternative to PHPUnit's `ArraySubset` constraint that was deprecated in PHPUnit 8 and removed in PHPUnit 9 - just way more powerful and less confusing. Refer to the ["Usage" section](#usage) and ["Example" section](#example) below for more info.

Made with :heart: by [Daniel Rudolf](https://www.daniel-rudolf.de). `PHPUnitArrayAssertions` is free and open source software, released under the terms of the [MIT license](https://github.com/PhrozenByte/phpunit-array-asserts/blob/master/LICENSE).

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

- By using the static class `PhrozenByte\PHPUnitArrayAsserts\Assert`
- By using the trait `PhrozenByte\PHPUnitArrayAsserts\ArrayAssertsTrait` in your test case
- By creating new constraint instances (`PhrozenByte\PHPUnitArrayAsserts\Constraint\…`)

All options do the same, the only difference is that the static class and trait both throw `PHPUnit\Framework\InvalidArgumentException` exceptions for invalid parameters. Creating new constraint instances is useful for advanced assertions, e.g. together with `PHPUnit\Framework\Constraint\LogicalAnd`.

### Constraint `AssociativeArray`

The `AssociativeArray` constraint asserts that a value is an associative array matching a given structure and that the array's items pass other constraints.

Any native array and `ArrayAccess` object is considered an associative array, no matter which keys they use. However, the array's items are applied to the matching constraint (parameter `$consotraints`). By default, additional items will fail the constraint (parameter `$allowAdditional`, defaults to `false`). The same is true when items are missing (parameter `$allowMissing`, defaults to `false`). The expected keys and constraints to apply, as well as whether additional and/or missing items should fail the constraint, are passed in the constructor.

**Usage:**

```php
// using `\PhrozenByte\PHPUnitArrayAsserts\ArrayAssertsTrait` trait
self::assertAssociativeArray(
    array $constraints,            // an associative array with the expected keys and constraints to apply
    array|ArrayAccess $array,      // the associative array to check
    bool $allowAdditional = false, // whether additional items should fail the constraint
    bool $allowMissing = false,    // whether missing items should fail the constraint
    string $message = ''           // additional information about the test
);

// using new instance of `\PhrozenByte\PHPUnitArrayAsserts\Constraint\AssociativeArray`
new AssociativeArray(
    array $constraints,
    bool $allowAdditional = false,
    bool $allowMissing = false
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
self::assertAssociativeArray([
    'id'      => new IsType(IsType::TYPE_INT),
    'name'    => new IsIdentical('Arthur Dent'),
    'options' => new AssociativeArray([ 'panic' => new IsType(IsType::TYPE_BOOL) ], true)
], $data);
```

### Constraint `ArrayHasKeyWith`

The `ArrayHasKeyWith` constraint asserts that an array has a given key and that its value passes another constraint.

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
self::ArrayHasKeyWith('name', new IsIdentical('Arthur Dent'), $data);
```

### Constraint `SequentialArray`

The `SequentialArray` constraint asserts that a value is like a sequential array, has a minimum and/or maximum number of items, and that all items pass another constraint.

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
self::assertSequentialArray($data, 1, null, self::logicalNot(new IsEmpty()));
```

### Constraint `ArrayHasItemWith`

The `ArrayHasItemWith` constraint asserts that an array has a item at a given index and that its value passes another constraint.

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
self::ArrayHasItemWith(2, new IsIdentical("Life, the Universe and Everything""));
```

Example
-------

```php
<?php
declare(strict_types=1);

namespace YourName\YourProject\Tests;

use PHPUnit\Framework\Constraint\IsIdentical;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\TestCase;
use PhrozenByte\PHPUnitArrayAsserts\ArrayAssertsTrait;
use PhrozenByte\PHPUnitArrayAsserts\Constraint\AssociativeArray;
use PhrozenByte\PHPUnitArrayAsserts\Constraint\SequentialArray;

class MyTest extends TestCase
{
    use ArrayAssertsTrait;

    public function testWithPHPUnitArrayAsserts(): void
    {
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

        $this->assertArrayHasKeyWith('users', new SequentialArray(1), $responseData);

        $this->assertAssociativeArray([
            'id'      => new IsType(IsType::TYPE_INT),
            'name'    => new IsIdentical('Arthur Dent'),
            'options' => new AssociativeArray([ 'panic' => new IsType(IsType::TYPE_BOOL) ], true)
        ], $responseData['users'][0]);

        // 7 lines of easier to understand code to check the API response *with* PHPUnitArrayAsserts
    }
    
    public function testWithoutPHPUnitArrayAsserts(): void
    {
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
        $this->assertEmpty(array_diff_key($userData, [ 'id' => true, 'name' => true, 'options' => true ]));

        $this->assertArrayHasKey('id', $userData);
        $this->assertIsInt($userData['id']);

        $this->assertArrayHasKey('name', $userData);
        $this->assertSame('Arthur Dent', $userData['name']);

        $this->assertArrayHasKey('options', $userData);
        $this->assertIsArray($userData['options']);

        $this->assertArrayHasKey('panic', $userData['options']);
        $this->assertIsBool($userData['options']['bool']);

        // 18 lines of pretty repetitive code to check the API response *without* PHPUnitArrayAsserts
    }
}
```
