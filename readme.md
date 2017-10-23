# ZA Phone

[![GitHub release](https://img.shields.io/github/release/mikerockett/za-phone.svg?style=flat-square)](https://github.com/mikerockett/za-phone/releases)
[![License](https://img.shields.io/github/license/mikerockett/za-phone.svg?style=flat-square)](https://github.com/mikerockett/za-phone/blob/master/LICENSE.md)
[![Travis](https://img.shields.io/travis/mikerockett/za-phone.svg?style=flat-square)](https://travis-ci.org/mikerockett/za-phone)

An easy-to-use validator and formatter for South African phone numbers.

## Installation

```
composer require rockett/za-phone
```

If you’re using Laravel 5.5, the service provider (which only provides validation features) will be detected automatically via package-discovery. You can opt out of this be adding the following to your app’s `composer.json`:

```json
"extra": {
    "laravel": {
        "dont-discover": [
            "rockett/za-phone"
        ]
    }
},
```


If you’d like to use validation and you’re on Laravel 5.4, add `Rockett\Toolkit\Providers\ZAPhoneServiceProvider::class` to your `config/app.php` `providers` array.

## Usage

First, import the class via real-time facade (no need to add it to `aliases`):

```php
use Facades\Rockett\Toolkit\ZAPhone;
```

Next, use the `check` method to see if the number is valid. If it returns `false`, then the number is not valid. Otherwise, the `$phone` variable will be ready to use.

```php
if (!$phone = ZAPhone::check('0125559999')) {
    // do something if the phone number is not valid
}
```

### Input Formats

You can use any format you like for the phone number, as it’ll strip out anything it doesn’t need. The purpose of this package is to validate *and* format the number according to your needs, and so it should be used on direct user input, say, from a form. Here’s a few examples of formats that’ll be accepted:

```
+27113330000
27 11 616 0223
011 323 1000
062525 9874
+27 (11) 525 9500
```

At the end of the day, *gobbledygook*-formatting (`+27 11/329.6200`) will be accepted, stripped, and validated. *But*, if the resulting number after stripping is not valid, then `check` will return `false`.

### Output Formatting

If the number is valid, you can then return the phone number in the format of your choosing. Available methods include:

```php
print $phone->formatNational();
// 012 555 9999

print $phone->formatE164();
// +27125559999

print $phone->formatIntl();
// +27 12 555 9999

print $phone->formatRFC3966();
// +27-12-555-9999
```

You can also format the number using another country’s exit-code. This will prefix the code to the number, showing how the number should be dialled from another country. The country code argument needs to be specified in [ISO 3166-1 alpha-3](https://en.wikipedia.org/wiki/ISO_3166-1_alpha-3) format.

```php
print $phone->formatDialIn('USA');
// 011 27 125559999 from the United States
print $phone->formatDialIn('AUS');
// 0011 27 125559999 from Australia
print $phone->formatDialIn('COL');
// 00444 27 125559999 from Colombia
print $phone->formatDialIn('RUS');
// 8p10 27 125559999 from Russia
```

> **Note:** `p` simply means that the caller should wait for the tone before continuing.

#### Casting to string

When casting the `$phone` instance to a string, it will default to the national format:

```php
print $phone;
// 012 555 9999
```

#### Options for the National format

You can also pass two optional arguments to the `formatNational` method.

The first argument, `$landlineParenthises`, which defaults to `false`, determines whether or not to wrap landline numbers in parenthises:

```php
print $phone->formatNational(true);
// (012) 555 9999
```

The second argument, `$hyphens`, which also defaults to `false`, determined whether or not to use hyphens to separate the digit groups. If the number is a landline and `$landlineParenthises` is set to `true`, one hyphen will separate the last two parts of the number. In all other cases, a hyphen will be used for all groups.

```php
print $phone->formatNational(true, true);
// (012) 555-9999
//
print $phone->formatNational(false, true);
// 012-555-9999
```

### Getters

Occasionally, you may need to access the different parts of the number. You can use the `number`, `prefix`, `three`, and `four` getters for this. Note, however, that the leading zero will not be included in `number` and `prefix`.

```php
print $phone->prefix;
// 12
```

### Validation

The package also makes a *Rule* and validator available, and each of them allow you to specify an optional format that the phone number should be provided by the user.

```php
'phone_number_field': 'required|zaphone',
'phone_number_field': ['required', Rule::zaphone()],
'phone_number_field': 'required|zaphone:national',
'phone_number_field': ['required', Rule::zaphone()->format('intl')],
```

> When using the `Rule` method, don’t forget to `use Illuminate\Validation\Rule;`. Note that `format()`’s parameter is case-sensitive, and accepts `national`, `intl`, `E164`, and `RFC3966`.

> In all honesty, forcing a format isn’t really necessary, given that the package is designed for you to validate any input that can be translated into a valid phone number ***and*** format it according to your app’s needs (database storage, etc.). As such, simply using the validation rule without forcing a format will suffice for most needs.

## Testing

Tests are powered by Orchestra’s test-bench. To test the package, simply navigate to its directory, run `composer install` and then `phpunit`.

## Need something more advanced?

ZA Phone is, as the name indicates, designed specifically for South African phone numbers and, therefore, is targeted at apps for use in the South African context. Whilst it has a somewhat limited feature-set, it should be suitable enough for most apps.

If you’re looking for something more complex and feature rich, be sure to check out [Laravel Phone](https://github.com/Propaganistas/Laravel-Phone).
