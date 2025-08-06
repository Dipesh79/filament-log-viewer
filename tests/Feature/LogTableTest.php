<?php

declare(strict_types=1);

namespace AchyutN\FilamentLogViewer\Tests\Feature;

use AchyutN\FilamentLogViewer\LogTable;

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
