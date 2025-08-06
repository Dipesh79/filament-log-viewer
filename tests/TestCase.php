<?php

declare(strict_types=1);

namespace AchyutN\FilamentLogViewer\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function writeLog(string $filename, string $content): void
    {
        file_put_contents(storage_path("logs/{$filename}"), $content);
    }
}
