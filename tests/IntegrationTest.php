<?php

namespace BeyondCode\VisualDiff\Tests;

use BeyondCode\VisualDiff\VisualDiffServiceProvider;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\ExpectationFailedException;

class IntegrationTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [VisualDiffServiceProvider::class];
    }

    protected function tearDown()
    {
        $files = glob(__DIR__.'/temp/*.png');
        array_map('unlink', $files);
    }

    /** @test */
    public function it_guesses_screenshot_name_from_test_name()
    {
        $this->app['config']->set('visualdiff.screenshot_path', __DIR__ . '/temp/');
        $this->app['config']->set('visualdiff.diff_path', __DIR__ . '/temp/');

        $this->get('/')->visualDiff();

        $path = config('visualdiff.screenshot_path');

        $this->assertTrue(file_exists($path . '1920_x_1080_it_guesses_screenshot_name_from_test_name.png'));
    }

    /** @test */
    public function it_creates_a_diff()
    {
        $this->app['config']->set('visualdiff.screenshot_path', __DIR__ . '/temp/');
        $this->app['config']->set('visualdiff.diff_path', __DIR__ . '/temp/');

        $this->get('/')->visualDiff();

        $this->app['router']->get('/', function(){
            return file_get_contents(__DIR__.'/fixtures/output.html');
        });

        try {
            $this->get('/')->visualDiff();
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

        $this->get('/')
            ->visualDiff();

        $path = config('visualdiff.diff_path');

        $this->assertTrue(file_exists($path . '320_x_240_it_creates_a_diff_for_multiple_resolutions.png'));
        $this->assertTrue(file_exists($path . '640_x_480_it_creates_a_diff_for_multiple_resolutions.png'));
    }

    /** @test */
    public function it_creates_a_diff_for_multiple_resolutions_with_helper()
    {
        $this->app['config']->set('visualdiff.screenshot_path', __DIR__ . '/temp/');
        $this->app['config']->set('visualdiff.diff_path', __DIR__ . '/temp/');

        $this->get('/')
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

        $path = config('visualdiff.diff_path');

        $this->assertTrue(file_exists($path . '320_x_240_it_creates_a_diff_for_multiple_resolutions_with_helper.png'));
        $this->assertTrue(file_exists($path . '640_x_480_it_creates_a_diff_for_multiple_resolutions_with_helper.png'));
    }
}
