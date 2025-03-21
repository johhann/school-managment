<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\Widget;

class TopPerformingStudents extends Widget
{
    protected static string $view = 'filament.widgets.top-performing-students';

    protected static ?int $sort = 3;

    public function getTopStudents()
    {
        return User::student()->withAvg('grades', 'grade')
            ->orderByDesc('grades_avg_grade')
            ->limit(5)
            ->get();
    }
}
