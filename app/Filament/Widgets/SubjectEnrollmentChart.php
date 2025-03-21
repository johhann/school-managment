<?php

namespace App\Filament\Widgets;

use App\Models\Subject;
use Filament\Widgets\ChartWidget;

class SubjectEnrollmentChart extends ChartWidget
{
    protected static ?string $heading = 'Student Enrollment Per Subject';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $subjects = Subject::withCount('students')->get();

        return [
            'datasets' => [
                [
                    'data' => $subjects->pluck('students_count')->toArray(),
                    'backgroundColor' => ['#6366F1', '#22C55E', '#F59E0B', '#EF4444', '#3B82F6'],
                ],
            ],
            'labels' => $subjects->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'responsive' => true,
            'height' => 300, // Set the height to 300px
        ];
    }
}
