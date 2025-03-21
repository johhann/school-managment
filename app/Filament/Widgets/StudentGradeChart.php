<?php

namespace App\Filament\Widgets;

use App\Models\Grade;
use Filament\Widgets\ChartWidget;

class StudentGradeChart extends ChartWidget
{
    protected static ?string $heading = 'Average Grade Per Subject';

    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $grades = Grade::selectRaw('subject_id, AVG(grade) as avg_grade')
            ->groupBy('subject_id')
            ->with('subject')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Average Grade',
                    'data' => $grades->pluck('avg_grade')->toArray(),
                    'backgroundColor' => 'info',
                ],
            ],
            'labels' => $grades->pluck('subject.name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
