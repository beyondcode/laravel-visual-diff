<?php

namespace BeyondCode\VisualDiff;

use Laravel\Dusk\Browser;
use Illuminate\Support\ServiceProvider;
use Illuminate\Testing\TestResponse;

class VisualDiffServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('visualdiff.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'visualdiff');

        TestResponse::macro('visualDiff', function ($name = null, $resolutions = null) {

            if (is_null($name)) {
                // Guess the test name from the backtrace
                $name = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)[4]['function'];
            }

            $testResolutions = config('visualdiff.resolutions');

            if (! is_null($resolutions)) {
                $testResolutions = $resolutions;
            }

            $tester = new VisualDiffTester($this->content(), $name, $testResolutions);

            $tester->setScreenshotOutputPath(config('visualdiff.screenshot_path'));
            $tester->setDiffOutputPath(config('visualdiff.diff_path'));

            $tester->createDiffs();

            return $this;
        });

        TestResponse::macro('visualDiffForResolutions', function (array $resolutions, $name = null) {
            if (is_null($name)) {
                // Guess the test name from the backtrace
                $name = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)[4]['function'];
            }

            return $this->visualDiff($name, $resolutions);
        });

        if (class_exists(Browser::class)) {
            Browser::macro('visualDiff', function ($name = null, $resolutions = null) {

                if (is_null($name)) {
                    // Guess the test name from the backtrace
                    $name = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 7)[6]['function'];
                }

                $testResolutions = config('visualdiff.resolutions');

                if (! is_null($resolutions)) {
                    $testResolutions = $resolutions;
                }

                $tester = new DuskVisualDiffTester('', $name, $testResolutions);
                $tester->setBrowser($this);

                $tester->setScreenshotOutputPath(config('visualdiff.screenshot_path'));
                $tester->setDiffOutputPath(config('visualdiff.diff_path'));

                $tester->createDiffs();

                return $this;
            });

            Browser::macro('visualDiffForResolutions', function (array $resolutions, $name = null) {
                if (is_null($name)) {
                    // Guess the test name from the backtrace
                    $name = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 7)[6]['function'];
                }

                return $this->visualDiff($name, $resolutions);
            });
        }
    }
}
