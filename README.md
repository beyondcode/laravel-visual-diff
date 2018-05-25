# Laravel Visual Diff

[![Latest Version on Packagist](https://img.shields.io/packagist/v/beyondcode/laravel-visual-diff.svg?style=flat-square)](https://packagist.org/packages/beyondcode/laravel-visual-diff)
[![Build Status](https://img.shields.io/travis/beyondcode/laravel-visual-diff/master.svg?style=flat-square)](https://travis-ci.org/beyondcode/laravel-visual-diff)
[![Quality Score](https://img.shields.io/scrutinizer/g/beyondcode/laravel-visual-diff.svg?style=flat-square)](https://scrutinizer-ci.com/g/beyondcode/laravel-visual-diff)
[![Total Downloads](https://img.shields.io/packagist/dt/beyondcode/laravel-visual-diff.svg?style=flat-square)](https://packagist.org/packages/beyondcode/laravel-visual-diff)

This package can create a visual diff of two screenshots of your Laravel application. It works for both - regular HTTP tests, as well as tests using Laravel Dusk.
Behind the scenes, it uses [Pixelmatch](https://github.com/mapbox/pixelmatch) to diff the two images.

Here are two basic examples of how the package works:

Using Laravel Dusk:

```php

class ExampleTest extends DuskTestCase
{
    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testBasicExample()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->visualDiff();
        });
    }
}

```

Using Laravel HTTP Testing:

```php

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicExample()
    {
        $this->get('/')
             ->visualDiff();
    }
}

```

## Requirements

This package requires node 7.6.0 or higher and the Pixelmatch Node library.

You can install the Pixelmatch on MacOS via NPM:

```bash
npm install pixelmatch
```

Or you could install it globally

```bash
npm install pixelmatch --global
```

### Custom node and npm binaries

Depending on your setup, node or npm might be not directly available to VisualDiff.
If you need to manually set these binary paths, you can do this by setting the `npm_binary` and `node_binary` config settings in the `visualdiff.php` configuration file.

## Installation

This package can be installed through Composer.

```bash
composer require beyondcode/laravel-visual-diff
```

## Usage

The package will automatically register the `VisualDiffServiceProvider` with your Laravel application.

The package adds a new method to either the `TestResponse` or the `Browser` class which is called `visualDiff`.
Depending on how you want to create visual diffs, follow the usage guidelines for the Laravel Dusk integration or the HTTP testing integration.

The `visualDiff` method accepts a name, that will be used for the screenshot generation. If you do not provide a name, the package will try and guess the test name. If this does not work for you, please provide a name manually instead.

### Dusk Integration

Just call the `visualDiff()` method on the Laravel Dusk `Browser` instance that you use for testing:

```php
$this->browse(function (Browser $browser) {
    $browser->visit('/')
            ->visualDiff();
});
``` 

This will automatically create a screenshot in all provided resolutions and create a diff, if a previous screenshot is available.

### HTTP Testing Integration

Just call the `visualDiff()` method on the `TestResponse` instance that you use for testing:

```php
$this->get('/')
     ->visualDiff();
``` 

This will automatically create a screenshot in all provided resolutions and create a diff, if a previous screenshot is available.

### Dealing with diffs

When VisualDiff detects differences in the new screenshot compared to the previous successfully created screenshot, the PHPUnit test will fail.
It will tell you which test caused the visual difference as well as giving you the filename of the screenshot diff.

Now you need to handle with this visual diff, just as you would with a code-diff. Review the changes and either approve the visual difference, or revert the UI state back to the successful state.

You can approve the new screenshots by adding a `-d --update-screenshots` flag to the phpunit command.

```bash
> ./vendor/bin/phpunit -d --update-screenshots

OK (1 test, 1 assertion)
``` 

### Specify multiple resolutions

Creating diffs for multiple resolutions can be very useful - especially if you want to test responsive websites and applications.

You can define all possible resolutions in your `visualdiff.php` configuration file:

```php
/**
 * Define all different resolutions that you want to use when performing
 * the regression tests.
 */
'resolutions' => [
    [
        'width' => 1920,
        'height'=> 1080
    ]
]
``` 

Alternatively,  you may want to only create one specific diff in multiple resolutions.
You can do this using the `visualDiffForResolutions` method and provide an array with the resolutions to test.

```php
$this->get('/')
     ->visualDiffForResolutions([
        [
            'width' => 1920,
            'height' => 1080
        ],
        [
            'width' => 640,
            'height' => 480
        ],
     ]);
```

### See diff results in your terminal window

If you use [iTerm2](https://www.iterm2.com/) as your terminal of choice, you will see an image representation of the diff when you run your tests.

![iTerm 2](https://beyondco.de/github/visualdiff.png)

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email marcel@beyondco.de instead of using the issue tracker.

## Credits

- [Marcel Pociot](https://github.com/mpociot)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
