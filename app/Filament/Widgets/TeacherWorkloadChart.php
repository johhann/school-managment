<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class TeacherWorkloadChart extends ChartWidget
{
    protected static ?string $heading = 'Teacher Workload';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $teachers = User::teacher()->withCount('teacherSubjects')->get();

        return [
            'datasets' => [
                [
                    'data' => $teachers->pluck('teacher_subjects_count')->toArray(),
                    'backgroundColor' => ['#EC4899', '#10B981', '#FBBF24', '#8B5CF6', '#F43F5E'],
                ],
            ],
            'labels' => $teachers->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
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
