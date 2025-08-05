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

    public string $unscopedLogLevel = 'all-logs';

    public function tabIsActive(string $tab): bool
    {
        if ($tab === $this->unscopedLogLevel) {
            return $this->activeTab === null || $this->activeTab === $this->unscopedLogLevel;
        }

        return $this->activeTab === $tab;
    }

    public function tableIsUnscoped(): bool
    {
        return in_array($this->activeTab, [null, $this->unscopedLogLevel], true);
    }

    /** @return array<string, mixed> */
    public function getTabs(): array
    {
        $all_logs = [
            $this->unscopedLogLevel => Tab::make('All Logs')
                ->id($this->unscopedLogLevel)
                ->badge(fn (): ?int => Log::getLogCount() ?: null),
        ];

        $tabs = collect(LogLevel::cases())
            ->mapWithKeys(fn (LogLevel $level) => [
                $level->value => Tab::make($level->getLabel())
                    ->id($level->value)
                    ->badge(
                        fn (): ?int => Log::getLogCount($level->value) ?: null,
                    )
                    ->badgeColor($level->getColor()),
            ])->toArray();

        return array_merge($all_logs, $tabs);
    }

    public function getActiveTab(): string
    {
        return $this->unscopedLogLevel;
    }
}
