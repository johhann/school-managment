<?php

namespace App\Filament\Resources\SubjectResource\RelationManagers;

use App\Models\Grade;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return DB::table('student_subject')->where('subject_id', $ownerRecord->id)->count();
    }

    public function form(Form $form): Form
    {
        $studentIds = DB::table('student_subject')
            ->where('subject_id', $this->getOwnerRecord()->id)
            ->pluck('student_id')
            ->toArray();

        return $form
            ->schema([
                Forms\Components\Select::make('student_id')
                    ->label('Select Student')
                    ->options(User::student()->whereNotIn('id', $studentIds)->pluck('name', 'id'))
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Enrolled at')
                    ->date(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Enroll New Student')
                    ->slideOver()
                    ->modalHeading('Enroll Student in Subject')
                    ->modalSubmitActionLabel('Enroll')
                    ->action(function (array $data) {
                        $this->getOwnerRecord()->students()->attach($data['student_id']);

                        Notification::make()
                            ->title('New student enrolled successfully')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->after(function ($record) {
                        Grade::where([
                            'student_id' => $record->id,
                            'subject_id' => $this->getOwnerRecord()->id,
                        ])->delete();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
