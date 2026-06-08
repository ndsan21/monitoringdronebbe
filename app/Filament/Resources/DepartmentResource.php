<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $navigationGroup = 'Master Data';
protected static ?string $navigationIcon = 'heroicon-o-briefcase';
protected static ?int $navigationSort = 3;
// Tambahkan ini di dalam class Resource yang ingin disembunyikan dari pilot

public static function canViewAny(): bool
{
    $user = auth()->user();

    if (! $user) {
        return false;
    }

    // 🔒 PENGUNCI PILOT: Hanya role super_admin dan admin yang boleh melihat/mengakses menu ini!
    return in_array($user->role, ['super_admin', 'admin']);
}

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Department Information')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Department Name')
                        ->placeholder('e.g., IT, Human Resources, Operation')
                        ->required()
                        ->unique(ignoreRecord: true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Department Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            // TOMBOL AKSEN TITIK TIGA DROP DOWN (MINIMALIS)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()->color('warning'),
                    Tables\Actions\DeleteAction::make(),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepartments::route('/'),
        ];
    }
}