<?php

declare(strict_types=1);

namespace AchyutN\FilamentLogViewer\Tests\Feature;

use AchyutN\FilamentLogViewer\LogTable;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->initializeLogs();
});

it('renders successfully', function () {
    livewire(LogTable::class)
        ->assertSuccessful()
        ->assertSee('Log Table');
});

it('has actions', function () {
    livewire(LogTable::class)
        ->assertActionExists('refresh', function (Action $action) {
            return $action->getLabel() === 'Refresh' &&
                $action->isOutlined();
        })
        ->assertActionExists('clear', function (Action $action) {
            return $action->getLabel() === 'Clear Logs' &&
                ! $action->isOutlined() &&
                $action->getColor() === Color::Red;
        });
});

it("refreshes logs on 'refresh' action", function () {
    livewire(LogTable::class)
        ->assertCountTableRecords(4)
        ->callAction('refresh')
        ->assertSuccessful()
        ->assertCountTableRecords(4);
});

it("clears logs on 'clear' action", function () {
    livewire(LogTable::class)
        ->assertCountTableRecords(4)
        ->callAction('clear')
        ->assertSuccessful()
        ->assertCountTableRecords(0);
});

it("show notification on 'clear' action", function () {
    livewire(LogTable::class)
        ->callAction('clear')
        ->assertSuccessful()
        ->mountAction('submit')
        ->assertNotified('Logs Cleared');

});

it('has table columns', function () {
    livewire(LogTable::class)
        ->assertTableColumnExists('date')
        ->assertTableColumnExists('env')
        ->assertTableColumnExists('log_level')
        ->assertTableColumnExists('message')
        ->assertTableColumnExists('file');
});

it('has badge in log_level column', function () {
    livewire(LogTable::class)
        ->assertCanRenderTableColumn('log_level')
        ->assertTableColumnExists('log_level', function (TextColumn $column) {
            return $column->isBadge();
        });
});

it('has Badge & Color in env column', function () {
    livewire(LogTable::class)
        ->assertCanNotRenderTableColumn('env')
        ->assertTableColumnExists('env', function (TextColumn $column) {
            return $column->isBadge() && $column->getColor('local') === Color::Blue;
        });
});

it('has table filters', function () {
    livewire(LogTable::class)
        ->assertTableFilterExists('date');
});

it('has indicators for date range', function () {
    livewire(LogTable::class)
        ->filterTable('date', [
            'from' => Carbon::create(2023)->toDateString(),
            'until' => Carbon::create(2023, 12, 31)->toDateString(),
        ])
        ->assertSeeText('Logs from Jan 1, 2023 to Dec 31, 2023');

    livewire(LogTable::class)
        ->filterTable('date', [
            'from' => Carbon::create(2023)->toDateString(),
            'until' => null,
        ])
        ->assertSeeText('Logs from Jan 1, 2023');

    livewire(LogTable::class)
        ->filterTable('date', [
            'from' => null,
            'until' => Carbon::create(2023, 12, 31)->toDateString(),
        ])
        ->assertSeeText('Logs until Dec 31, 2023');

    livewire(LogTable::class)
        ->filterTable('date', [
            'from' => null,
            'until' => null,
        ])
        ->assertDontSeeText('Logs from')
        ->assertDontSeeText('Logs until');
});
