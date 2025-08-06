<?php

declare(strict_types=1);

use AchyutN\FilamentLogViewer\Model\Log;

beforeEach(function () {
    if (! is_dir(storage_path('logs'))) {
        mkdir(storage_path('logs'), 0755, true);
    }

    $this->writeLog('laravel.log', '[2024-08-06 20:15:00] local.ERROR: Sample log');
    $this->writeLog('other.log', '[2024-08-06 20:16:00] local.INFO: Another log');
    $this->writeLog('not-a-log.txt', 'This is not a log');

    $stackTraces = [
        '[2024-08-06 20:17:00] local.ERROR: Sample log with stack trace at /path/to/file.php:123',
        '[stacktrace]',
        '#0 /path/to/another_file.php(456): someFunction()',
        '#1 {main}',
        '"}',
        '[2024-08-06 20:18:00] local.ERROR: Another log with stack trace at /path/to/another_file.php:789',
        '[stacktrace]',
        '#0 /path/to/yet_another_file.php(101): anotherFunction()',
        '#1 {main}',
        '"}',
    ];
    $this->writeLog('stack-trace.log', implode("\n", $stackTraces));
});

afterEach(function () {
    collect(glob(storage_path('logs/*')))->each(fn ($file) => unlink($file));
});

describe('log files', function () {
    it('has logs in files with .log extension', function () {
        $laravelLog = file_get_contents(storage_path('logs/laravel.log'));
        $stackTraceLog = file_get_contents(storage_path('logs/stack-trace.log'));

        expect($laravelLog)
            ->toContain('local.ERROR: Sample log');

        expect($stackTraceLog)
            ->toContain('local.ERROR: Sample log with stack trace');
    });
});

describe('destroyAllLogs', function () {
    it('clears contents of all .log files', function () {
        Log::destroyAllLogs();

        $laravelLog = file_get_contents(storage_path('logs/laravel.log'));
        $otherLog = file_get_contents(storage_path('logs/other.log'));
        $notALog = file_get_contents(storage_path('logs/not-a-log.txt'));
        $stackTraceLog = file_get_contents(storage_path('logs/stack-trace.log'));

        expect($laravelLog)->toBe('');
        expect($otherLog)->toBe('');
        expect($notALog)->toBe('This is not a log');
        expect($stackTraceLog)->toBe('');
    });

    it('does nothing if log folder does not exist', function () {
        collect(glob(storage_path('logs/*')))->each(fn ($file) => unlink($file));

        expect(fn () => Log::destroyAllLogs())->not->toThrow(Exception::class);
    });
});

describe('getRows', function () {
    it('returns all logs from .log files', function () {
        $logs = Log::getRows();

        expect($logs)->toBeArray();
        expect($logs)->toHaveCount(4);
        expect($logs)
            ->each
            ->toHaveKey('date')
            ->toHaveKey('env')
            ->toHaveKey('log_level')
            ->toHaveKey('message')
            ->toHaveKey('stack')
            ->toHaveKey('file');
        expect($logs)
            ->sequence(
                function ($log) {
                    return $log
                        ->date->toBe('2024-08-06 20:15:00')
                        ->env->toBe('local')
                        ->log_level->tobe(AchyutN\FilamentLogViewer\Enums\LogLevel::ERROR)
                        ->message->toBe('Sample log')
                        ->stack->toBeNull()
                        ->file->toBe('laravel.log');
                },
                function ($log) {
                    return $log
                        ->date->toBe('2024-08-06 20:16:00')
                        ->env->toBe('local')
                        ->log_level->tobe(AchyutN\FilamentLogViewer\Enums\LogLevel::INFO)
                        ->message->toBe('Another log')
                        ->stack->toBeNull()
                        ->file->toBe('other.log');
                },
                function ($log) {
                    return $log
                        ->date->toBe('2024-08-06 20:17:00')
                        ->env->toBe('local')
                        ->log_level->tobe(AchyutN\FilamentLogViewer\Enums\LogLevel::ERROR)
                        ->message->toBe('Sample log with stack trace at /path/to/file.php:123')
                        ->stack->not->toBeNull()
                        ->file->toBe('stack-trace.log');
                },
                function ($log) {
                    return $log
                        ->date->toBe('2024-08-06 20:18:00')
                        ->env->toBe('local')
                        ->log_level->tobe(AchyutN\FilamentLogViewer\Enums\LogLevel::ERROR)
                        ->message->toBe('Another log with stack trace at /path/to/another_file.php:789')
                        ->stack->not->toBeNull()
                        ->file->toBe('stack-trace.log');
                }
            );
    });

    it('returns an empty array if no log files exist', function () {
        collect(glob(storage_path('logs/*')))->each(fn ($file) => unlink($file));

        $logs = Log::getRows();

        expect($logs)->toBeArray();
        expect($logs)->toBeEmpty();
    });
});
