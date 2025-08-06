<?php

declare(strict_types=1);

use AchyutN\FilamentLogViewer\Filters\DateRangeFilter;

it('renders DateRangeFilter', function () {
    $filter = DateRangeFilter::make('test_filter');

    expect($filter)->toBeInstanceOf(Filament\Tables\Filters\Filter::class);
    expect($filter->getName())->toBe('test_filter');
    expect($filter->getLabel())->toBe('Date Range');
    expect($filter->getColumns())->toBe(2);
});
