<?php

namespace App\Filament\Widgets;

use App\Models\Grade;
use Filament\Widgets\Widget;

class RecentGrades extends Widget
{
    protected static string $view = 'filament.widgets.recent-grades';

    protected static ?int $sort = 2;

    public function getRecentGrades()
    {
        return Grade::with(['student', 'subject', 'teacher'])
            ->latest()
            ->limit(5)
            ->get();
    }
}
