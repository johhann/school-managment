<x-filament::widget>
    <x-filament::card>
        <h2 class="text-lg font-bold">Top Performing Students</h2>
        <ul class="mt-2">
            @foreach ($this->getTopStudents() as $student)
                <li class="border-b py-2">
                    <strong>{{ $student->name }}</strong> - Avg Grade:
                    <span class="font-bold">{{ number_format($student->grades_avg_grade, 2) }}</span>
                </li>
            @endforeach
        </ul>
    </x-filament::card>
</x-filament::widget>
