<?php

return [

    /**
     * The node binary to use for Pixelmatch / Browsershot.
     * Use null to fallback to the default values.
     */
    'node_binary' => null,

    /**
     * The npm binary to use for Pixelmatch / Browsershot.
     * Use null to fallback to the default values.
     */
    'npm_binary' => null,

    /**
     * The path where all screenshots will be stored.
     */
    'screenshot_path' => base_path('tests/VisualRegression/screenshots'),

    /**
     * The path where diff result images will be stored.
     */
    'diff_path' => base_path('tests/VisualRegression/diffs'),

    /**
     * The threshold used for the pixelmatch diffing process.
     */
    'threshold' => 0.01,

    /**
     * Determines if the antialiasing flag of the pixelmatch
     * diffing process should be used.
     */
    'antialias' => true,

    /**
     * The maximum allowed error percentage before PHPUnit triggers an error.
     */
    'maximum_error_percentage' => 0.0,

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
];