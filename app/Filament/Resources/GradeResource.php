<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GradeResource\Pages;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;

class GradeResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Grade::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_id')
                    ->relationship('student', 'name')
                    ->required(),
                Forms\Components\Select::make('subject_id')
                    ->relationship('subject', 'name')
                    ->required(),
                Forms\Components\Select::make('teacher_id')
                    ->relationship('teacher', 'name'),
                Forms\Components\TextInput::make('grade')
                    ->minValue(0)
                    ->maxValue(100)
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subject.name')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('student.name')
                    ->description(fn ($record) => $record->student->email)
                    ->visible(Auth::user()->can('view_student_grade'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('teacher.name')
                    ->description(fn ($record) => $record->teacher->email)
                    ->visible(Auth::user()->can('view_teacher_grade'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('grade')
                    ->numeric()
                    ->default('Not Graded')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->date()
                    ->sortable()
                    ->searchable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                SelectFilter::make('subject_id')
                    ->label('Subject')
                    ->relationship('subject', 'name')
                    ->options([Subject::pluck('name', 'id')]),
                SelectFilter::make('teacher_id')
                    ->label('Teacher')
                    ->relationship('teacher', 'name')
                    ->options([User::teacher()->pluck('name', 'id')])
                    ->hidden(Auth::user()->hasRole('teacher')),
                SelectFilter::make('student_id')
                    ->label('Student')
                    ->relationship('student', 'name')
                    ->options(User::student()->pluck('name', 'id'))
                    ->hidden(Auth::user()->hasRole('student')),
            ], FiltersLayout::AboveContent)
            ->modifyQueryUsing(function (Builder $query) {
                if (Auth::user()->hasRole('student')) {
                    return $query->where('student_id', Auth::id());
                }

                if (Auth::user()->hasRole('teacher')) {
                    return $query->where('teacher_id', Auth::id());
                }

                return $query;
            })
            ->filtersFormColumns(3)
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('Update Grade')
                    ->label('Update')
                    ->slideOver()
                    ->visible(Auth::user()->can('update_grade'))
                    ->color('primary')
                    ->icon('heroicon-o-cursor-arrow-rays')
                    ->form(function ($record) {
                        return [
                            Forms\Components\TextInput::make('grade')
                                ->minValue(0)
                                ->maxValue(100)
                                ->default($record->grade)
                                ->numeric(),
                        ];
                    })
                    ->modalHeading('Add Grade')
                    ->action(function (array $data, $record) {
                        $record->grade = $data['grade'];
                        $record->save();

                        Notification::make()
                            ->title('Grade set successfully')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->visible(Auth::user()->can('delete_grade')),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGrades::route('/'),
            'create' => Pages\CreateGrade::route('/create'),
            'edit' => Pages\EditGrade::route('/{record}/edit'),
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
            'view_student',
            'view_teacher',
        ];
    }
}
