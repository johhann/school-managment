<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubjectResource\Pages;
use App\Filament\Resources\SubjectResource\RelationManagers\StudentsRelationManager;
use App\Filament\Resources\SubjectResource\RelationManagers\TeachersRelationManager;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubjectResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Subject::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('teachers_count')
                    ->counts('teachers')
                    ->visible(Auth::user()->hasAnyRole('admin', 'teacher'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('students_count')
                    ->counts('students')
                    ->visible(Auth::user()->hasAnyRole('admin', 'teacher'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('enrolled_at')
                    ->state(fn ($record) => $record->students->first()->created_at)
                    ->visible(Auth::user()->hasRole('student'))
                    ->date(),
                Tables\Columns\TextColumn::make('assigned_at')
                    ->state(fn ($record) => $record->students->first()->created_at)
                    ->visible(Auth::user()->hasRole('teacher'))
                    ->date(),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                if (Auth::user()->hasRole('student')) {
                    $studentSubjects = DB::table('student_subject')
                        ->where('student_id', Auth::id())
                        ->pluck('subject_id');

                    return $query->whereIn('id', $studentSubjects)
                        ->with(['students' => fn ($q) => $q->limit(1)]);
                }

                if (Auth::user()->hasRole('teacher')) {
                    $teacherSubjects = DB::table('teacher_subject')
                        ->where('teacher_id', Auth::id())
                        ->pluck('subject_id');

                    return $query->whereIn('id', $teacherSubjects)
                        ->with(['teachers' => fn ($q) => $q->limit(1)]);
                }

                return $query;
            })
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('Add Teacher')
                    ->icon('heroicon-o-user-plus')
                    ->iconButton()
                    ->color('info')
                    ->visible(Auth::user()->can('create_teacher_subject'))
                    ->form(function ($record) {
                        $teacherIds = DB::table('teacher_subject')->where('subject_id', $record->id)->pluck('teacher_id')->toArray();

                        return [
                            Forms\Components\Select::make('teacher_id')
                                ->label('Select Teacher')
                                ->options(User::teacher()->whereNotIn('id', $teacherIds)->pluck('name', 'id'))
                                ->required(),
                        ];
                    })
                    ->action(function (array $data, Subject $record) {
                        $record->teachers()->attach($data['teacher_id']);

                        Notification::make()
                            ->title('New student enrolled successfully')
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Assign Teacher to Subject')
                    ->modalSubmitActionLabel('Add Teacher')
                    ->slideOver(),

                Tables\Actions\Action::make('Add Student')
                    ->icon('heroicon-o-user-group')
                    ->iconButton()
                    ->color('info')
                    ->visible(Auth::user()->can('create_student_subject'))
                    ->form(function ($record) {
                        $studentIds = DB::table('student_subject')
                            ->where('subject_id', $record->id)
                            ->pluck('student_id');

                        $teacherIds = DB::table('teacher_subject')
                            ->where('subject_id', $record->id)
                            ->pluck('teacher_id');

                        return [
                            Forms\Components\Select::make('student_id')
                                ->label('Select Student')
                                ->options(User::student()->whereNotIn('id', $studentIds)->pluck('name', 'id'))
                                ->required(),
                            Forms\Components\Select::make('teacher_id')
                                ->label('Select Teacher')
                                ->options(User::teacher()->whereIn('id', $teacherIds)->pluck('name', 'id'))
                                ->default(function () {
                                    if (Auth::user()->isTeacher()) {
                                        return Auth::id();
                                    }

                                    return null;
                                })
                                ->disabled(function () {
                                    if (Auth::user()->isTeacher()) {
                                        return Auth::id();
                                    }

                                    return false;
                                })
                                ->required(),
                        ];
                    })
                    ->action(function (array $data, Subject $record) {
                        if (Auth::user()->isTeacher()) {
                            $data['teacher_id'] = Auth::id();
                        }
                        $record->students()->syncWithoutDetaching($data['student_id']);
                        Grade::firstOrCreate([
                            'student_id' => $data['student_id'],
                            'subject_id' => $record->id,
                        ], [
                            'teacher_id' => $data['teacher_id'],
                        ]);

                        Notification::make()
                            ->title('New student enrolled successfully')
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Enroll Student in Subject')
                    ->modalSubmitActionLabel('Enroll')
                    ->slideOver(),
                Tables\Actions\ViewAction::make()
                    ->visible(Auth::user()->can('view_grade'))
                    ->slideOver()
                    ->iconButton(),
                Tables\Actions\EditAction::make()
                    ->visible(Auth::user()->can('update_subject'))
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            StudentsRelationManager::class,
            TeachersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubjects::route('/'),
            'create' => Pages\CreateSubject::route('/create'),
            'edit' => Pages\EditSubject::route('/{record}/edit'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'create_student',
            'create_teacher',
        ];
    }
}
