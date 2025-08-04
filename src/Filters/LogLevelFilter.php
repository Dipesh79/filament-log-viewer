<?php

declare(strict_types=1);

namespace AchyutN\FilamentLogViewer\Filters;

use AchyutN\FilamentLogViewer\Enums\LogLevel;
use Exception;
use Filament\Tables\Filters\SelectFilter;

final class LogLevelFilter
{
    /** @throws Exception */
    public static function make(string $name = 'log_level'): SelectFilter
    {
        return SelectFilter::make($name)
            ->multiple()
            ->searchable()
            ->options(LogLevel::class)
            ->label('Log Level')
            ->indicator('Log Level');
    }
}
