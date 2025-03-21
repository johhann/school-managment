<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubjectResource\Pages;
use App\Filament\Resources\SubjectResource\RelationManagers\StudentsRelationManager;
use App\Filament\Resources\SubjectResource\RelationManagers\TeachersRelationManager;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class SubjectResource extends Resource
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
                    ->searchable(),
                Tables\Columns\TextColumn::make('students_count')
                    ->counts('students')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->slideOver()
                    ->iconButton(),
                Tables\Actions\EditAction::make()
                    ->iconButton(),
                Tables\Actions\Action::make('Add Teacher')
                    ->icon('heroicon-o-user-plus')
                    ->iconButton()
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
                    ->form(function ($record) {
                        $studentIds = DB::table('student_subject')->where('student_id', $record->id)->pluck('student_id')->toArray();
                        $teacherIds = DB::table('teacher_subject')->where('subject_id', $record->id)->pluck('teacher_id')->toArray();

                        return [
                            Forms\Components\Select::make('student_id')
                                ->label('Select Student')
                                ->options(User::student()->whereNotIn('id', $studentIds)->pluck('name', 'id'))
                                ->required(),
                            Forms\Components\Select::make('teacher_id')
                                ->label('Select Teacher')
                                ->options(User::teacher()->whereIn('id', $teacherIds)->pluck('name', 'id'))
                                ->required(),
                        ];
                    })
                    ->action(function (array $data, Subject $record) {
                        $record->students()->attach($data['student_id']);
                        Grade::create([
                            'student_id' => $data['student_id'],
                            'teacher_id' => $data['teacher_id'],
                            'subject_id' => $record->id,
                        ]);

                        Notification::make()
                            ->title('New student enrolled successfully')
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Enroll Student in Subject')
                    ->modalSubmitActionLabel('Enroll')
                    ->slideOver(),
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
}
