<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class EpicsRelationManager extends RelationManager
{
    protected static bool $isLazy = true;

    protected static string $relationship = 'epics';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return $ownerRecord->epics_count ?? $ownerRecord->epics()->count();
    }

    protected static ?string $title = 'Epics';

    protected static ?string $label = 'Epic';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label(__('Epic Name')),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->label(__('Sort Order'))
                    ->helperText(__('Lower numbers display first')),
                DatePicker::make('start_date')
                    ->label(__('Start Date'))
                    ->nullable(),
                DatePicker::make('end_date')
                    ->label(__('End Date'))
                    ->nullable(),
                RichEditor::make('description')
                    ->label(__('Description'))
                    ->columnSpanFull()
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('attachments')
                    ->fileAttachmentsVisibility('public')
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(fn ($query) => $query->select(['id', 'project_id', 'name', 'sort_order', 'start_date', 'end_date', 'created_at']))
            ->columns([
                TextColumn::make('sort_order')
                    ->label(__('Sort Order'))
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('Epic Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label(__('Start Date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label(__('End Date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('tickets_count')
                    ->counts('tickets')
                    ->label(__('Number of tickets')),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()->label(__('Create New Epic')),
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
            ])
            ->defaultSort('sort_order', 'asc');
    }
}
