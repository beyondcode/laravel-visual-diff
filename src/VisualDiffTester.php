<?php

namespace BeyondCode\VisualDiff;

use PHPUnit\Framework\Assert;
use Spatie\Browsershot\Browsershot;
use PHPUnit\Framework\ExpectationFailedException;

class VisualDiffTester
{
    protected $html;

    protected $name;

    protected $currentResolution = null;

    protected $resolutions;

    protected $diffOutputPath = null;

    protected $screenshotOutputPath = null;

    public function __construct(string $html, string $name, array $resolutions)
    {
        $this->html = $html;
        $this->name = $name;
        $this->resolutions = $resolutions;
        $this->screenshotOutputPath = config('visualdiff.screenshot_path');
        $this->diffOutputPath = config('visualdiff.diff_path');
    }

    public function setDiffOutputPath(string $diffOutputPath)
    {
        $this->diffOutputPath = $diffOutputPath;

        return $this;
    }

    public function setScreenshotOutputPath(string $screenshotOutputPath)
    {
        $this->screenshotOutputPath = $screenshotOutputPath;

        return $this;
    }

    public function createDiffs()
    {
        $this->preparePaths();

        foreach ($this->resolutions as $resolution) {

            $this->currentResolution = $resolution;

            $createDiff = $this->shouldCreateDiff();

            $this->createScreenshot();

            if ($createDiff) {
                $this->createDiff();
            }

        }
    }

    protected function preparePaths()
    {
        @mkdir($this->screenshotOutputPath, 0755, true);
        @mkdir($this->diffOutputPath, 0755, true);
    }

    protected function shouldCreateDiff(): bool
    {
        return file_exists($this->screenshotOutputPath . DIRECTORY_SEPARATOR . $this->currentResolution['width'] . '_x_' . $this->currentResolution['height'] . '_' . $this->name . '.png');
    }

    protected function getDiffFilename()
    {
        return $this->currentResolution['width'] . '_x_' . $this->currentResolution['height'] . '_diff_' . $this->name . '.png';
    }

    protected function getComparisonFilename()
    {
        return $this->currentResolution['width'] . '_x_' . $this->currentResolution['height'] . '_' . $this->name . '.png';
    }

    protected function getNewFilename()
    {
        return $this->currentResolution['width'] . '_x_' . $this->currentResolution['height'] . '_new_' . $this->name . '.png';
    }

    protected function getFilename()
    {
        if (!$this->shouldCreateDiff()) {
            return $this->getComparisonFilename();
        }

        return $this->getNewFilename();
    }

    /**
     * @throws \Spatie\Browsershot\Exceptions\CouldNotTakeBrowsershot
     */
    protected function createScreenshot()
    {
        $filename = $this->getFilename();

        $browsershot = Browsershot::html($this->html);
        $browsershot->noSandbox();

        if (! is_null(config('visualdiff.node_binary'))) {
            $browsershot->setNodeBinary(config('visualdiff.node_binary'));
        }

        if (! is_null(config('visualdiff.npm_binary'))) {
            $browsershot->setNpmBinary(config('visualdiff.npm_binary'));
        }

        $browsershot->windowSize($this->currentResolution['width'], $this->currentResolution['height'])
            ->save($this->screenshotOutputPath . DIRECTORY_SEPARATOR . $filename);
    }
    /**
     * Determines whether or not the screenshots should be updated instead of
     * matched.
     *
     * @return bool
     */
    protected function shouldUpdateScreenshots(): bool
    {
        return in_array('--update-screenshots', $_SERVER['argv'], true);
    }

    protected function createDiff()
    {
        $diff = VisualDiff::diff(
            $this->screenshotOutputPath . DIRECTORY_SEPARATOR . $this->getNewFilename(),
            $this->screenshotOutputPath . DIRECTORY_SEPARATOR . $this->getComparisonFilename()
        );

        if (! is_null(config('visualdiff.node_binary'))) {
            $diff->setNodeBinary(config('visualdiff.node_binary'));
        }

        if (! is_null(config('visualdiff.npm_binary'))) {
            $diff->setNpmBinary(config('visualdiff.npm_binary'));
        }

        $diff->setAntialias(config('visualdiff.antialias'))
            ->setThreshold(config('visualdiff.threshold'));

        $result = $diff->save($this->diffOutputPath . DIRECTORY_SEPARATOR . $this->getDiffFilename());

        if (! is_null($result)) {
            try {
                Assert::assertLessThanOrEqual(
                    config('visualdiff.maximum_error_percentage'),
                    $result->error_percentage,
                    "The visual diff for " . $this->name . " has a higher pixel diff than the allowed maximum." . PHP_EOL .
                    "See: " . $this->diffOutputPath . $this->getDiffFilename()
                );
            } catch (ExpectationFailedException $e) {
                if ($this->shouldUpdateScreenshots()) {
                    $this->renameScreenshots();
                    return;
                } else {
                    echo "\n\n" . shell_exec(__DIR__ . '/../bin/imgcat ' . escapeshellarg($this->diffOutputPath . DIRECTORY_SEPARATOR . $this->getDiffFilename()));

                    throw $e;
                }
            }
        }

        // Rename new image for next comparison
        $this->renameScreenshots();
    }

    protected function renameScreenshots()
    {
        rename(
            $this->screenshotOutputPath . DIRECTORY_SEPARATOR . $this->getNewFilename(),
            $this->screenshotOutputPath . DIRECTORY_SEPARATOR . $this->getComparisonFilename()
        );
    }

}