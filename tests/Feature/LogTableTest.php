<?php

declare(strict_types=1);

namespace AchyutN\FilamentLogViewer\Tests\Feature;

use AchyutN\FilamentLogViewer\LogTable;
use Carbon\Carbon;

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
        ->assertActionExists('refresh')
        ->assertActionExists('clear');
});

it('has table columns', function () {
    livewire(LogTable::class)
        ->assertTableColumnExists('date')
        ->assertTableColumnExists('log_level')
        ->assertTableColumnExists('message');
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
