<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Grade;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required(),
                Forms\Components\Select::make('role_id')
                    ->relationship('roles', 'name')
                    ->required(),
                Forms\Components\TextInput::make('password')
                    ->required()
                    ->password()
                    ->revealable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->badge()
                    ->copyable()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->searchable()
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined at')
                    ->date()
                    ->sortable(),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                if (Auth::user()->hasRole('student')) {
                    return $query->where('id', Auth::id());
                }

                if (Auth::user()->hasRole('teacher')) {
                    $students = Grade::where('teacher_id', Auth::id())->pluck('student_id');

                    // dd($students);
                    return $query->whereIn('id', $students);
                }

                return $query;
            })
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('Admin')
                    ->toggle()
                    ->visible(Auth::user()->can('view_user'))
                    ->modifyQueryUsing(fn (Builder $query) => $query->admin()),
                Filter::make('Teacher')
                    ->toggle()
                    ->visible(Auth::user()->can('view_user'))
                    ->modifyQueryUsing(fn (Builder $query) => $query->teacher()),
                Filter::make('Student')
                    ->toggle()
                    ->visible(Auth::user()->can('view_user'))
                    ->modifyQueryUsing(fn (Builder $query) => $query->student()),
            ], FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->persistFiltersInSession()
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
