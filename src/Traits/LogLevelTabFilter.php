<?php

declare(strict_types=1);

namespace AchyutN\FilamentLogViewer\Traits;

use AchyutN\FilamentLogViewer\Enums\LogLevel;
use AchyutN\FilamentLogViewer\Model\Log;
use Filament\Resources\Concerns\HasTabs;
use Filament\Schemas\Components\Tabs\Tab;

trait LogLevelTabFilter
{
    use HasTabs;

    /** @return array<string, mixed> */
    public function getTabs(): array
    {
        $all_logs = [
            'all-logs' => Tab::make('All Logs')
                ->id('all-logs')
                ->badge(fn () => Log::query()->count() ?: null),
        ];

        $tabs = collect(LogLevel::cases())
            ->mapWithKeys(fn (LogLevel $level) => [
                $level->value => Tab::make($level->getLabel())
                    ->id($level->value)
                    ->badge(
                        fn () => Log::query()->where('log_level', $level)->count() ?: null
                    )
                    ->badgeColor($level->getColor()),
            ])->toArray();

        return array_merge($all_logs, $tabs);
    }

    public function getActiveTab(): string
    {
        return 'all-logs';
    }
}
