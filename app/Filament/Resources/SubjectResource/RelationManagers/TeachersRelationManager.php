<?php

namespace App\Filament\Resources\SubjectResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TeachersRelationManager extends RelationManager
{
    protected static string $relationship = 'teachers';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return DB::table('teacher_subject')->where('subject_id', $ownerRecord->id)->count();
    }

    public function form(Form $form): Form
    {
        $teacherIds = DB::table('teacher_subject')
            ->where('subject_id', $this->getOwnerRecord()->id)
            ->pluck('teacher_id')
            ->toArray();

        return $form
            ->schema([
                Forms\Components\Select::make('teacher_id')
                    ->label('Select Teacher')
                    ->options(User::teacher()->whereNotIn('id', $teacherIds)->pluck('name', 'id'))
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
                    ->label('Assigned at')
                    ->date(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Assign New Teacher')
                    ->slideOver()
                    ->modalHeading('Assign Teacher to Subject')
                    ->modalSubmitActionLabel('Assign')
                    ->action(function (array $data) {
                        $this->getOwnerRecord()->teachers()->attach($data['teacher_id']);

                        Notification::make()
                            ->title('Teacher assigned successfully')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
