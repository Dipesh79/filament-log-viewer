<?php

declare(strict_types=1);

namespace AchyutN\FilamentLogViewer;

use AchyutN\FilamentLogViewer\Filters\DateRangeFilter;
use AchyutN\FilamentLogViewer\Model\Log;
use AchyutN\FilamentLogViewer\Traits\LogLevelTabFilter;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class LogTable extends Page implements HasTable
{
    use InteractsWithTable;
    use LogLevelTabFilter;

    protected string $view = 'filament-log-viewer::log-table';

    /** @throws Exception */
    public static function getNavigationLabel(): string
    {
        return self::getPlugin()->getNavigationLabel();
    }

    /** @throws Exception */
    public static function getNavigationGroup(): string
    {
        return self::getPlugin()->getNavigationGroup();
    }

    /** @throws Exception */
    public static function getNavigationSort(): int
    {
        return self::getPlugin()->getNavigationSort();
    }

    /** @throws Exception */
    public static function getSlug(?Panel $panel = null): string
    {
        return self::getPlugin()->getNavigationUrl();
    }

    /** @throws Exception */
    public static function getNavigationIcon(): string
    {
        return self::getPlugin()->getNavigationIcon();
    }

    /** @throws Exception */
    public static function canAccess(): bool
    {
        return self::getPlugin()->isAuthorized();
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return $table
            ->records(
                fn (?string $sortColumn, ?string $sortDirection): Collection => collect(Log::getRows())
            )
            ->modifyQueryUsing(function (Builder $query): void {
                if (! $this->tableIsUnscoped()) {
                    $query->where('log_level', $this->activeTab);
                }
            })
            ->columns([
                TextColumn::make('log_level')
                    ->badge(),
                TextColumn::make('env')
                    ->label('Environment')
                    ->color(fn (string $state): array => match ($state) {
                        'local' => Color::Blue,
                        'production' => Color::Red,
                        'staging' => Color::Orange,
                        'testing' => Color::Gray,
                        default => Color::Yellow
                    })
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->badge(),
                TextColumn::make('file')
                    ->label('File Name')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('message')
                    ->searchable()
                    ->label('Summary')
                    ->wrap(),
                TextColumn::make('date')
                    ->label('Occurred')
                    ->since()
                    ->dateTimeTooltip()
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make('view')
                    ->schema([
                        RepeatableEntry::make('stack')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('trace')
                                    ->hiddenLabel()
                                    ->columnSpanFull(),
                            ])
                            ->label('Stack Trace'),
                    ])
                    ->slideOver(),
            ])
            ->poll(self::getPlugin()->getPollingTime())
            ->filters(
                [
                    DateRangeFilter::make('date'),
                ]
            )
            ->filtersFormWidth(Width::ExtraLarge)
            ->filtersFormColumns(1)
            ->deferFilters(false)
            ->deferColumnManager(false)
            ->defaultSort('date', 'desc');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh')
                ->icon(Heroicon::ArrowPath)
                ->outlined()
                ->action(function (): void {
                    $this->refresh();
                }),
            Action::make('clear')
                ->label('Clear Logs')
                ->icon(Heroicon::Trash)
                ->color(Color::Red)
                ->requiresConfirmation()
                ->action(function (): void {
                    Log::destroyAllLogs();
                    Notification::make()
                        ->title('Logs Cleared')
                        ->success()
                        ->send();
                }),
        ];
    }

    /** @throws Exception */
    private static function getPlugin(): FilamentLogViewer
    {
        return filament('filament-log-viewer');
    }
}
