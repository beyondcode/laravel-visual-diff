<?php

namespace BeyondCode\VisualDiff\Tests;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\Browser;
use Orchestra\Testbench\Dusk\TestCase;
use Facebook\WebDriver\Chrome\ChromeOptions;
use PHPUnit\Framework\ExpectationFailedException;
use BeyondCode\VisualDiff\VisualDiffServiceProvider;

class DuskIntegrationTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [VisualDiffServiceProvider::class];
    }

    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions())->addArguments([
            '--no-sandbox',
            '--disable-gpu',
            '--headless',
            '--window-size=1920, 1080',
        ]);

        return RemoteWebDriver::create(
            'http://localhost:9515', DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }

    protected function tearDown()
    {
        $files = glob(__DIR__.'/temp/*.png');
        array_map('unlink', $files);
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['router']->get('hello', ['as' => 'hi', 'uses' => function () {
            return 'hello world';
        }]);

        $app['router']->get('diff', ['as' => 'hi', 'uses' => function () {
            return 'Something else';
        }]);
    }

    /** @test */
    public function it_guesses_screenshot_name_from_test_name()
    {
        $this->app['config']->set('visualdiff.screenshot_path', __DIR__ . '/temp/');
        $this->app['config']->set('visualdiff.diff_path', __DIR__ . '/temp/');

        $this->browse(function (Browser $browser) {
            $browser->visit('hello')
                    ->visualDiff();
        });

        $path = config('visualdiff.screenshot_path');

        $this->assertTrue(file_exists($path . '1920_x_1080_it_guesses_screenshot_name_from_test_name.png'));
    }

    /** @test */
    public function it_creates_a_diff()
    {
        $this->app['config']->set('visualdiff.screenshot_path', __DIR__ . '/temp/');
        $this->app['config']->set('visualdiff.diff_path', __DIR__ . '/temp/');


        $this->browse(function (Browser $browser) {
            $browser->visit('hello')
                ->visualDiff();
        });

        try {
            $this->browse(function (Browser $browser) {
                $browser->visit('diff')
                    ->visualDiff();
            });
        } catch (ExpectationFailedException $e) {
            $this->assertContains(
                'The visual diff for it_creates_a_diff has a higher pixel diff than the allowed maximum',
                $e->getMessage()
            );
        }

        $path = config('visualdiff.diff_path');

        $this->assertTrue(file_exists($path . '1920_x_1080_diff_it_creates_a_diff.png'));
    }

    /** @test */
    public function it_creates_a_diff_for_multiple_resolutions()
    {
        $this->app['config']->set('visualdiff.screenshot_path', __DIR__ . '/temp/');
        $this->app['config']->set('visualdiff.diff_path', __DIR__ . '/temp/');

        $this->app['config']->set('visualdiff.resolutions', [
            [
                'width' => 320,
                'height' => 240
            ],
            [
                'width' => 640,
                'height' => 480
            ]
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('hello')
                ->visualDiff();
        });

        $path = config('visualdiff.diff_path');

        $this->assertTrue(file_exists($path . '320_x_240_it_creates_a_diff_for_multiple_resolutions.png'));
        $this->assertTrue(file_exists($path . '640_x_480_it_creates_a_diff_for_multiple_resolutions.png'));
    }

    /** @test */
    public function it_creates_a_diff_for_multiple_resolutions_with_helper()
    {
        $this->app['config']->set('visualdiff.screenshot_path', __DIR__ . '/temp/');
        $this->app['config']->set('visualdiff.diff_path', __DIR__ . '/temp/');

        $this->browse(function (Browser $browser) {
            $browser->visit('hello')
                ->visualDiffForResolutions([
                    [
                        'width' => 320,
                        'height' => 240
                    ],
                    [
                        'width' => 640,
                        'height' => 480
                    ]
                ]);
        });

        $path = config('visualdiff.diff_path');

        $this->assertTrue(file_exists($path . '320_x_240_it_creates_a_diff_for_multiple_resolutions_with_helper.png'));
        $this->assertTrue(file_exists($path . '640_x_480_it_creates_a_diff_for_multiple_resolutions_with_helper.png'));
    }
}
