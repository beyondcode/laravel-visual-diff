<?php

namespace BeyondCode\VisualDiff;

use Symfony\Component\Process\Process;

class VisualDiff
{
    protected $nodeBinary = null;

    protected $npmBinary = null;

    protected $includePath = '$PATH:/usr/local/bin';

    protected $binPath = null;

    protected $nodeModulePath = null;

    protected $newImage;

    protected $comparisonImage;

    protected $threshold = 0.1;

    protected $antialias = false;


    public function setNodeBinary(string $nodeBinary)
    {
        $this->nodeBinary = $nodeBinary;

        return $this;
    }

    public function setNpmBinary(string $npmBinary)
    {
        $this->npmBinary = $npmBinary;

        return $this;
    }

    public function setIncludePath(string $includePath)
    {
        $this->includePath = $includePath;

        return $this;
    }

    public function setBinPath(string $binPath)
    {
        $this->binPath = $binPath;

        return $this;
    }

    public function setNodeModulePath(string $nodeModulePath)
    {
        $this->nodeModulePath = $nodeModulePath;

        return $this;
    }

    public function setAntialias(bool $antialias)
    {
        $this->antialias = $antialias;

        return $this;
    }

    public function setThreshold(float $threshold)
    {
        $this->threshold = $threshold;

        return $this;
    }

    protected function getNodePathCommand(string $nodeBinary): string
    {
        if ($this->nodeModulePath) {
            return "NODE_PATH='{$this->nodeModulePath}'";
        }
        if ($this->npmBinary) {
            return "NODE_PATH=`{$nodeBinary} {$this->npmBinary} root -g`";
        }
        return 'NODE_PATH=`npm root -g`';
    }

    public function __construct(string $newImage, string $comparisonImage)
    {
        $this->newImage = $newImage;
        $this->comparisonImage = $comparisonImage;
    }

    public static function diff(string $newImage, string $comparisonImage)
    {
        return new static($newImage, $comparisonImage);
    }

    public function buildSaveCommand($filename): array
    {
        return [
            'image_1' => $this->newImage,
            'image_2' => $this->comparisonImage,
            'output' => $filename,
            'threshold' => $this->threshold,
            'antialias' => $this->antialias,
        ];
    }

    public function save($filename)
    {
        $output = $this->callDiff($this->buildSaveCommand($filename));

        return json_decode($output);
    }

    protected function callDiff(array $command)
    {
        $setIncludePathCommand = "PATH={$this->includePath}";

        $nodeBinary = $this->nodeBinary ?: 'node';

        $setNodePathCommand = $this->getNodePathCommand($nodeBinary);

        $binPath = $this->binPath ?: __DIR__ . '/../bin/diff.js';

        $process = new Process([
            $nodeBinary,
            $binPath,
            json_encode($command)
        ]);
        $process->run();

        if ($process->isSuccessful()) {
            return rtrim($process->getOutput());
        }
    }
}