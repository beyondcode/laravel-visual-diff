<?php

namespace BeyondCode\VisualDiff\Tests;


use BeyondCode\VisualDiff\VisualDiff;
use PHPUnit\Framework\TestCase;

class VisualDiffTest extends TestCase
{
    /** @test */
    public function it_can_create_save_commands()
    {
        $diff = new VisualDiff('file-1', 'file-2');

        $command = $diff->buildSaveCommand('output');

        $this->assertSame([
            'image_1' => 'file-1',
            'image_2' => 'file-2',
            'output' => 'output',
            'threshold' => 0.1,
            'antialias' => false,
        ], $command);
    }

    /** @test */
    public function it_can_be_created_statically()
    {
        $diff = VisualDiff::diff('file-1', 'file-2');

        $command = $diff->buildSaveCommand('output');

        $this->assertSame([
            'image_1' => 'file-1',
            'image_2' => 'file-2',
            'output' => 'output',
            'threshold' => 0.1,
            'antialias' => false,
        ], $command);
    }

    /** @test */
    public function it_can_set_threshold()
    {
        $diff = VisualDiff::diff('file-1', 'file-2');

        $diff->setThreshold(0.4);

        $command = $diff->buildSaveCommand('output');

        $this->assertArraySubset([
            'threshold' => 0.4,
        ], $command);
    }

    /** @test */
    public function it_can_set_antialiasing()
    {
        $diff = VisualDiff::diff('file-1', 'file-2');

        $diff->setAntialias(true);

        $command = $diff->buildSaveCommand('output');

        $this->assertArraySubset([
            'antialias' => true,
        ], $command);
    }

    /** @test */
    public function it_returns_the_diff_output()
    {
        $file1 = __DIR__ . '/fixtures/new.png';
        $file2 = __DIR__ . '/fixtures/comparison.png';

        $output = __DIR__ . '/temp/out.png';

        $output = VisualDiff::diff($file1, $file2)
            ->save($output);

        $this->assertSame(184, $output->pixels);
        $this->assertSame(7.36, $output->error_percentage);
    }
}
