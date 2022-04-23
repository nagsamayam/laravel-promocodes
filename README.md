# laravel-promocodes

Coupons and promotional codes generator for [Laravel](https://laravel.com). This package requires [Laravel 9.x](https://laravel.com/docs/9.x) and [PHP 8.1](https://www.php.net/releases/8.1/en.php).

## Installation

You can install the package via composer:

```bash
composer require nagsamayam/laravel-promocodes
```

## Configuration

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-promocodes-config"
```

This is the contents of the published config file:

```php
return [
    'models' => [
        'promocodes' => [
            'model' => \NagSamayam\Promocodes\Models\Promocode::class,
            'table_name' => 'promocodes',
            'foreign_id' => 'promocode_id',
        ],

        'users' => [
            'model' => \App\Models\User::class,
            'table_name' => 'users',
            'foreign_id' => 'user_id',
        ],

        'pivot' => [
            'model' => \NagSamayam\Promocodes\Models\PromocodeUser::class,
            'table_name' => 'promocode_user',
        ],
    ],
    'code_mask' => '**-****-****',
    'allowed_symbols' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789',
];
```

After you configure this file, publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-promocodes-migrations"
php artisan migrate
```

Now you will need to use AppliesPromocode on your user model.

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use NagSamayam\Promocodes\Traits\AppliesPromocode;

class User extends Authenticatable
{
    use AppliesPromocode;

    //
}
```

## Usage

It's very easy to use. Methods are combined, so that you can configure promocodes easily.

-   [Reference](#reference)
-   [Creating Promocodes](#creating-promocodes)
-   [Generating Promocodes](#generating-promocodes)
-   [Applying Promocode](#applying-promocode)
-   [Expiring Promocode](#expiring-promocode)
-   [Applicable Discount](#get-applicable-discount)
-   [Note](#note)

## Reference

| Name            | Explanation                                                                                                               |
| --------------- | ------------------------------------------------------------------------------------------------------------------------- |
| Mask            | Astrisks will be replaced with random symbol                                                                              |
| Characters      | Allowed symbols to use in mask replacement                                                                                |
| Type            | Promocode type. flat or percent                                                                                           |
| Multi use       | Define if single code can be used multiple times, by the same user                                                        |
| Unlimited       | Generated code will have unlimited usages                                                                                 |
| Bound to user   | Define if promocode can be used only one user, if user is not assigned initially, first user will be bound to promocode   |
| User            | Define user who will be initially bound to promocode                                                                      |
| Count           | Amount of unique promocodes should be generated                                                                           |
| Usages          | Define how many times can promocode be used                                                                               |
| Expiration      | DateTime when promocode should be expired. Null means that promocode will never expire                                    |
| Details         | Array of details which will be retrieved upon apply. `discount` key for `flat` type. `percent_off` key for `percent` type |
| Min order value | Define order minimum value                                                                                                |
| Max discount    | Define maximum discount to be applied for an order.                                                                       |

## Creating Promocodes

### Using class

Combine methods as you need. You can skip any method that you don't need, most of them already have default values. For custom code generation, `create` method should have argument. Or you can use `createWithCustomCode` with argument. Both the options works.

```php
use NagSamayam\Promocodes\Facades\Promocodes;
use NagSamayam\Promocodes\Enums\PromocodeType;

Promocodes::mask('AA-***-BB') // default: config('promocodes.code_mask')
    ->characters('ABCDE12345') // default: config('promocodes.allowed_symbols')
    ->multiUse() // default: false
    ->unlimited() // default: false
    ->boundToUser() // default: false
    ->user(User::find(1)) // default: null
    ->count(5) // default: 1
    ->expiration(now()->addYear()) // default: null
    ->details(['percent_off' => 50]) // default: []
    ->minOrderValue(500) // default: null
    ->maxDiscount(5) // default: null
    ->createdByAdmin(User::find(1)) // default: null
    ->type(PromocodeType::PERCENT->value) // default: PromocodeType::FLAT->value
    ->create(); // default: null

Promocodes::multiUse() // default: false
    ->boundToUser() // default: false
    ->user(User::find(1)) // default: null
    ->usages(5) // default: 1
    ->expiration(now()->addYear()) // default: null
    ->details(['discount' => 50]) // default: []
    ->minOrderValue(500) // default: null
    ->maxDiscount(5) // default: null
    ->createdByAdmin(User::find(1)) // default: null
    ->type(PromocodeType::FLAT->value) // default: PromocodeType::FLAT->value
    ->create('AA-A4C-B1'); // default: null // Custom promo code can be passed // Equivalent to createWithCustomCode('MO-OT2P-897P') method

Promocodes::multiUse() // default: false
    ->boundToUser() // default: false
    ->user(User::find(1)) // default: null
    ->usages(5) // default: 1
    ->expiration(now()->addYear()) // default: null
    ->details([
        'percent_off' => 50,
        'message' => 'This one is for you. I hope you like it',
    ]) // default: []
    ->minOrderValue(500) // default: null
    ->maxDiscount(5) // default: null
    ->createdByAdmin(User::find(1)) // default: null
    ->type(PromocodeType::PERCENT->value) // default: PromocodeType::FLAT->value
    ->createWithCustomCode('MO-OT2P-897P');
```

### Using helper

There is a global helper function which will do the same as promocodes class. You can use named arguments magic from php
8.1.

```php
createPromocodes(
    type: PromocodeType::PERCENT->value, // default: PromocodeType::FLAT->value
    mask: 'AA-***-BB', // default: config('promocodes.code_mask')
    characters: 'ABCDE12345', // default: config('promocodes.allowed_symbols')
    multiUse: true, // default: false
    unlimited: true, // default: false
    boundToUser: true, // default: false
    user: User::find(1), // default: null
    count: 5, // default: 1
    expiration: now()->addYear(), // default: null
    details: ['percent_off' => 50], // default: [],
    createdByAdmin: User::find(98), // default: null
  	maxDiscount: 101, // default: null
  	minOrderValue: 1000, // default: null
);

createCustomPromocode(
    code: '66-OMPY-ULZU',
    type: PromocodeType::FLAT->value, // default: PromocodeType::FLAT->value
    multiUse: true, // default: false
    boundToUser: true, // default: false
    user: User::find(1), // default: null
    usages: 5, // default: 1
    expiration: now()->addYear(), // default: null
    details: ['discount' => 50], // default: [],
    createdByAdmin: User::find(98), // default: null
  	maxDiscount: 101, // default: null
  	minOrderValue: 1000 // default: null
);
```

### Generating Promocodes

If you want to output promocodes and not save them to database, you can call generate method instead of create.

```php
use NagSamayam\Promocodes\Facades\Promocodes;
use NagSamayam\Promocodes\Enums\PromocodeType;

Promocodes::mask('AA-***-BB') // default: config('promocodes.code_mask')
    ->characters('ABCDE12345') // default: config('promocodes.allowed_symbols')
    ->multiUse() // default: false
    ->unlimited() // default: false
    ->boundToUser() // default: false
    ->user(User::find(1)) // default: null
    ->count(5) // default: 1
    ->expiration(now()->addYear()) // default: null
    ->details(['percent_off' => 50]) // default: []
    ->minOrderValue(500)
    ->maxDiscount(5)
    ->createdByAdmin(User::find(1))
    ->type(PromocodeType::PERCENT->value) // default: PromocodeType::FLAT->value
    ->generate();
```

### Applying Promocode

### Using class

Combine methods as you need. You can skip any method that you don't need.

```php
use NagSamayam\Promocodes\Facades\Promocodes;

Promocodes::code('ABC-DEF')
    ->user(User::find(1)) // default: null
    ->apply(['order_id' => '405-6828433-4214765']); // default: null // Good to send unique order ID // Preferbly string format
```

### Using helper

There is a global helper function which will do the same as promocodes class.

```php
applyPomocode(
    'ABC-DEF',
    User::find(1), // default: null,
    ['order_id' => '405-6828433-4214765'] // default: null // Good to send unique order ID // Preferbly string format
);
```

#### Exceptions

While trying to apply promocode, you should be aware of exceptions. Most part of the code throws exceptions, when there
is a problem:

```php
// NagSamayam\Promocodes\Exceptions\*

PromocodeAlreadyUsedByUserException - "The given code `ABC-DEF` is already used by user with id 1."
PromocodeBoundToOtherUserException - "The given code `ABC-DEF` is bound to other user, not user with id 1."
PromocodeDoesNotExistException - "The given code `ABC-DEF` doesn't exist." | "The code was not event provided."
PromocodeExpiredException - "The given code `ABC-DEF` already expired."
PromocodeNoUsagesLeftException - "The given code `ABC-DEF` has no usages left."
UserHasNoAppliesPromocodeTrait - "The given user model doesn't have AppliesPromocode trait."
UserRequiredToAcceptPromocode - "The given code `ABC-DEF` requires to be used by user, not by guest."
PromocodeAlreadyExistedException - "The given promocode `10s%discount` is already existed. Please try with a new one."
PromocodeAlreadyUsedForOrderException - "The given code `ABC-DEF` is already applied for the order with id 405-6828433-4214765."
```

#### Events

There are two events which are fired upon applying.

```php
// NagSamayam\Promocodes\Events\*

GuestAppliedPromocode // Fired when guest applies promocode
    // It has public variable: promocode

UserAppliedPromocode // Fired when user applies promocode
    // It has public variable: promocode
    // It has public variable: user
```

### Expiring Promocode

### Using helper

There is a global helper function which will expire promocode.

```php
expirePromocode('ABC-DEF');
```

## Trait Methods

If you added AppliesPromocode trait to your user model, you will have some additional methods on user.

```php
$user = User::find(1);

$user->appliedPromocodes; // Returns promocodes applied by user
$user->boundPromocodes; // Returns promocodes bound to user
$user->applyPromocode('ABC-DEF', ['order_id' => '405-6828433-4214765']); // Applies promocode to user
```

#### Get applicable discount

There is a way to get the discount to be applied on total order value.

```php
use NagSamayam\Promocodes\Facades\Promocodes;

Promocodes::code('ABC-DEF')
    ->orderTotal(1000) // default: 0
    ->getApplicableDiscount($forceCheck = true); // default: false // If false, it will not check for usagesLeft and expiry
```

## Testing

Will be added very soon

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/nagsamayam/laravel-promocodes/blob/master/CONTRIBUTING.md) for details.

## Security Vulnerabilities

If you discover any security related issues, please email nag.samayam@gmail.com instead of using the issue tracker.

## Credits

-   [Nageswara Rao](https://github.com/nagsamayam)
-   [All Contributors](../../contributors)

This package is heavily based on the Laravel Promocodes package from [Zura Gabievi](https://github.com/zgabievi). You can find the code on [GitHub](https://github.com/zgabievi/laravel-promocodes).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Note

I have developed this package for my personal use. I made this public. Hope it helps you. Please do not expect the immediate bug fixes. Hope you understand.
