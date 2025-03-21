<?php

namespace App\Filament\Widgets;

use App\Models\Grade;
use Filament\Widgets\ChartWidget;

class GradeDistributionChart extends ChartWidget
{
    protected static ?string $heading = 'Student Grade Distribution';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $gradeRanges = [
            '0-50' => [0, 50],
            '51-60' => [51, 60],
            '61-70' => [61, 70],
            '71-80' => [71, 80],
            '81-90' => [81, 90],
            '91-100' => [91, 100],
        ];

        $gradeCounts = [];

        foreach ($gradeRanges as $label => [$min, $max]) {
            $gradeCounts[] = Grade::whereBetween('grade', [$min, $max])->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Number of Students',
                    'data' => $gradeCounts,
                    'backgroundColor' => ['#EF4444', '#F59E0B', '#FACC15', '#4ADE80', '#3B82F6', '#8B5CF6'],
                ],
            ],
            'labels' => array_keys($gradeRanges),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'responsive' => true,
            'height' => 400,
            'width' => '100%',
        ];
    }
}
