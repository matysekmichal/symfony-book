<?php

namespace App\Services\ImageOptimizer;

interface ImageOptimizerInterface
{
    public function resize(string $filename): void;
}
