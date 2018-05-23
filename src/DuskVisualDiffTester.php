<?php

namespace BeyondCode\VisualDiff;

use Laravel\Dusk\Browser;

class DuskVisualDiffTester extends VisualDiffTester
{
    /** @var Browser */
    protected $browser;

    public function setBrowser(Browser $browser)
    {
        $this->browser = $browser;

        return $this;
    }

    protected function createScreenshot()
    {
        $filename = str_replace('.png', '', $this->getFilename());

        $this->browser
            ->resize($this->currentResolution['width'], $this->currentResolution['height'])
            ->screenshot($filename);
    }

    public function setScreenshotOutputPath(string $screenshotOutputPath)
    {
        parent::setScreenshotOutputPath($screenshotOutputPath);

        Browser::$storeScreenshotsAt = $screenshotOutputPath;
    }
}