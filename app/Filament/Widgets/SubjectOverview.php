<?php

namespace App\Filament\Widgets;

use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class SubjectOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Card::make('Total Subjects', Subject::count())
                ->description('All subjects available')
                ->icon('heroicon-o-book-open'),

            Card::make('Total Students', User::student()->count())
                ->description('Total students enrolled')
                ->color('success')
                ->icon('heroicon-o-users'),

            Card::make('Total Teachers', User::teacher()->count())
                ->description('Total assigned teachers')
                ->color('info')
                ->icon('heroicon-o-academic-cap'),

            Card::make('Average Grade', number_format(Grade::avg('grade'), 2))
                ->description('Overall student performance')
                ->color('warning')
                ->icon('heroicon-o-chart-bar'),
        ];
    }
}
