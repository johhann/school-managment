<x-filament::widget>
    <x-filament::card>
        <h2 class="text-lg font-bold">Recent Grades</h2>
        <ul class="mt-2">
            @foreach ($this->getRecentGrades() as $grade)
                <li class="border-b py-2">
                    <strong>{{ $grade->student->name }}</strong> got
                    <span class="font-bold">{{ $grade->grade }}</span> in
                    <strong>{{ $grade->subject->name }}</strong>
                    (Taught by {{ $grade->teacher->name }})
                </li>
            @endforeach
        </ul>
    </x-filament::card>
</x-filament::widget>
