<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GradeResource\Pages;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GradeResource extends Resource
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
                Tables\Columns\TextColumn::make('student.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('teacher.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grade')
                    ->numeric()
                    ->default('Not Graded')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('subject_id')
                    ->relationship('subject', 'name')
                    ->options([Subject::pluck('name', 'id')]),
                SelectFilter::make('teacher_id')
                    ->relationship('teacher', 'name')
                    ->options([User::teacher()->pluck('name', 'id')]),
                SelectFilter::make('student_id')
                    ->relationship('student', 'name')
                    ->options([User::student()->pluck('name', 'id')]),
            ], FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('Update Grade')
                    ->slideOver()
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
                Tables\Actions\DeleteAction::make(),
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
}
