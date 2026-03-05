<?php

namespace App\Filament\Resources\TicketPriorities;

use App\Filament\Resources\TicketPriorities\Pages\CreateTicketPriority;
use App\Filament\Resources\TicketPriorities\Pages\EditTicketPriority;
use App\Filament\Resources\TicketPriorities\Pages\ListTicketPriorities;
use App\Models\TicketPriority;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TicketPriorityResource extends Resource
{
    protected static ?string $model = TicketPriority::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationLabel = 'Ticket Priorities';

    protected static ?string $pluralLabel = 'Ticket Priorities';

    public static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    public static function getModelLabel(): string
    {
        return __('Ticket Priority');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Ticket Priorities');
    }

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Priority Name'))
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                ColorPicker::make('color')
                    ->label(__('Color'))
                    ->required()
                    ->default('#6B7280'),
            ]);
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Priority Name'))
                    ->searchable()
                    ->sortable(),
                ColorColumn::make('color')
                    ->label(__('Color'))
                    ->sortable(),
                TextColumn::make('tickets_count')
                    ->counts('tickets')
                    ->label(__('Tickets'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Updated At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('Delete Selected')),
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
            'index' => ListTicketPriorities::route('/'),
            'create' => CreateTicketPriority::route('/create'),
            'edit' => EditTicketPriority::route('/{record}/edit'),
        ];
    }
}
